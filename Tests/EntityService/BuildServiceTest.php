<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\EntityService;

use Ctrl\RadBundle\Entity\User;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\EntityService\BuildService;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Doctrine\ORM\EntityManager;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Description of BuildServiceTest
 *
 * @author Jelle Sebreghts
 */
class BuildServiceTest extends TestCase
{

    use DataGenerator;

    protected $workspaceDirectory;
    protected $kernelDir;

    /**
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrine;

    /**
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    /**
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $processBuilder;

    protected function setUp()
    {
        parent::setUp();
        $this->workspaceDirectory = $this->getAlphaNumeric();
        $this->kernelDir = $this->getAlphaNumeric();
        $this->doctrine = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->container = $this->getMockBuilder(ContainerInterface::class)->disableOriginalConstructor()->getMock();
        $this->processBuilder = $this->getMockBuilder(ProcessBuilder::class)->disableOriginalConstructor()->getMock();
    }

    public function testConstructor()
    {
        $service = new BuildService($this->workspaceDirectory, $this->kernelDir);
        $service->setDoctrine($this->doctrine);
        $service->setContainer($this->container);
        $this->assertEquals($this->workspaceDirectory, $service->getWorkspaceDirectory());
        $this->assertEquals($this->kernelDir, $service->getKernelDir());
    }

    public function testGetEntityClass()
    {
        $service = $this->getService();
        $this->assertEquals(Build::class, $service->getEntityClass());
    }

    public function testGetContainer()
    {
        $service = $this->getService();
        $this->assertEquals($this->container, $service->getContainer());
    }

    public function testSetWorkspaceDirectory()
    {
        $service = $this->getService();
        $workspaceDirectory = $this->getAlphaNumeric();
        $service->setWorkspaceDirectory($workspaceDirectory);
        $this->assertEquals($workspaceDirectory, $service->getWorkspaceDirectory());
    }

    public function testUpdateBuildLog() {
        $service = $this->getService();
        $build = $this->getMockBuilder(Build::class)->disableOriginalConstructor()->getMock();
        $buildId = uniqid();
        $build->expects($this->any())->method('getId')->willReturn($buildId);
        $originalLog = $this->getAlphaNumeric();
        $newMessage = $this->getAlphaNumeric();
        $build->expects($this->once())->method('getLog')->willReturn($originalLog);
        $build->expects($this->once())->method('setLog')->with($this->callback(function ($log) use ($originalLog, $newMessage) {
            return strpos($log, $originalLog) !== false && strpos($log, $newMessage) !== false;
        }));
        $this->doctrine
            ->expects($this->once())->method('persist')
            ->with(
                $this->callback(
                    function (Build $build) use ($buildId)
                    {
                        return $build->getId() === $buildId;
                    }
                )
            );
        $service->updateBuildLog($build, $newMessage);
    }

    public function testCreateNewBackgroundProvisionAll()
    {
        $service = $this->getService();

        $appId = uniqid();
        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getId')->willReturn($appId);

        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $app->expects($this->once())->method('setProvisionBuild')->with($this->callback(
            function (Build $build) use ($user, $app) {
                return $build->getUser() === $user
                    && $build->getApplication() === $app
                    && $build->getType() === Build::TYPE_PROVISION;
            }
        ));

        $token = $this->getMockBuilder(TokenInterface::class)->disableOriginalConstructor()->getMock();
        $token->expects($this->once())->method('getUser')->willReturn($user);

        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->disableOriginalConstructor()->getMock();
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $this->container->expects($this->once())->method('get')->with('security.token_storage')->willReturn($tokenStorage);

        $buildId = uniqid();

        $this->doctrine
            ->expects($this->once())->method('persist')
            ->with(
                $this->callback(
                    function (Build $build) use ($user, $app)
                    {
                        return $build->getType() === Build::TYPE_PROVISION
                            && $build->getApplication() === $app
                            && $build->getUser() === $user;
                    }
                )
            )
            ->willReturnCallback(function (Build $build) use ($buildId)
            {
                $refObject = new ReflectionObject($build);
                $refProperty = $refObject->getProperty('id');
                $refProperty->setAccessible(true);
                $refProperty->setValue($build, $buildId);
                return $build;
            });

        $process = $this->getMockBuilder(Process::class)->disableOriginalConstructor()->getMock();
        $process->expects($this->once())->method('run');

        $this->processBuilder
            ->expects($this->once())
            ->method('setPrefix')
            ->with("exec php {$this->kernelDir}/../bin/console digip:provision -b$buildId -a -- $appId > /dev/null 2>&1 &");
        $this->processBuilder->expects($this->once())->method('getProcess')->willReturn($process);

        $build = $service->createNewBackgroundProvision($app);

        $this->assertEquals($buildId, $build->getId());
        $this->assertEquals(Build::TYPE_PROVISION, $build->getType());
        $this->assertEquals($app, $build->getApplication());
    }

    /**
     * @dataProvider provisionOptionsProvider
     */
    public function testCreateNewBackgroundProvisionOptions($options)
    {
        $expectedMask = 0;
        $commandOptions = [];
        foreach ($options as $option => $mask)
        {
            $expectedMask |= $mask;
            $commandOptions[] = $option;
        }
        sort($commandOptions);
        $opts = implode('', $commandOptions);

        $service = $this->getService();

        $appId = uniqid();
        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getId')->willReturn($appId);

        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $app->expects($this->once())->method('setProvisionBuild')->with($this->callback(
            function (Build $build) use ($user, $app) {
                return $build->getUser() === $user
                    && $build->getApplication() === $app
                    && $build->getType() === Build::TYPE_PROVISION;
            }
        ));

        $token = $this->getMockBuilder(TokenInterface::class)->disableOriginalConstructor()->getMock();
        $token->expects($this->once())->method('getUser')->willReturn($user);

        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->disableOriginalConstructor()->getMock();
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $this->container->expects($this->once())->method('get')->with('security.token_storage')->willReturn($tokenStorage);

        $buildId = uniqid();

        $this->doctrine
            ->expects($this->once())->method('persist')
            ->with(
                $this->callback(
                    function (Build $build) use ($user, $app)
                    {
                        return $build->getType() === Build::TYPE_PROVISION
                            && $build->getApplication() === $app
                            && $build->getUser() === $user;
                    }
                )
            )
            ->willReturnCallback(function (Build $build) use ($buildId)
            {
                $refObject = new ReflectionObject($build);
                $refProperty = $refObject->getProperty('id');
                $refProperty->setAccessible(true);
                $refProperty->setValue($build, $buildId);
                return $build;
            });

        $process = $this->getMockBuilder(Process::class)->disableOriginalConstructor()->getMock();
        $process->expects($this->once())->method('run');

        $this->processBuilder
            ->expects($this->once())
            ->method('setPrefix')
            ->with("exec php {$this->kernelDir}/../bin/console digip:provision -b$buildId -$opts -- $appId > /dev/null 2>&1 &");
        $this->processBuilder->expects($this->once())->method('getProcess')->willReturn($process);

        $build = $service->createNewBackgroundProvision($app, $expectedMask);

        $this->assertEquals($buildId, $build->getId());
        $this->assertEquals(Build::TYPE_PROVISION, $build->getType());
        $this->assertEquals($app, $build->getApplication());

    }

    public function provisionOptionsProvider()
    {
        $options = array(
            'j' => BuildService::PROVISION_CI,
            'J' => BuildService::PROVISION_CI_OVERRIDE,
            'f' => BuildService::PROVISION_FILESYSTEM,
            'c' => BuildService::PROVISION_CONFIG_FILES,
            'S' => BuildService::PROVISION_SOCK,
            'C' => BuildService::PROVISION_CRON,
            'a' => BuildService::PROVISION_ALL,
        );
        $data = array();
        // Test each option individually
        foreach ($options as $name => $mask)
        {
            $data[] = array(array($name => $mask));
        }

        unset($options['a']);

        // Test random combinations.
        for ($i = 0; $i < 10; $i++)
        {
            $randomOptions = array_rand($options, mt_rand(2, count($options) - 1));
            $keys = array_combine($randomOptions, $randomOptions);
            $data[] = array(array_intersect_key($options, $keys));
        }
        return $data;
    }

    /**
     *
     * @return BuildService
     */
    protected function getService()
    {
        $service = new BuildService($this->workspaceDirectory, $this->kernelDir, $this->processBuilder);
        $service->setDoctrine($this->doctrine);
        $service->setContainer($this->container);
        return $service;
    }

}
