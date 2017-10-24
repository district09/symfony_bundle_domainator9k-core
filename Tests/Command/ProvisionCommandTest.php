<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Command;

use Ctrl\Common\EntityService\Finder\FinderInterface;
use Ctrl\Common\EntityService\Finder\ResultInterface;
use DigipolisGent\Domainator9k\CoreBundle\Command\ProvisionCommand;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application as ApplicationEntity;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\EntityService\ApplicationService;
use DigipolisGent\Domainator9k\CoreBundle\EntityService\BuildService;
use DigipolisGent\Domainator9k\CoreBundle\EntityService\ServerService;
use DigipolisGent\Domainator9k\CoreBundle\EntityService\SettingsService;
use Symfony\Bundle\WebProfilerBundle\Tests\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of ProvisionCommandTest
 *
 * @author Jelle Sebreghts
 */
class ProvisionCommandTest extends TestCase
{

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $this->build = $this->getMockBuilder(Build::class)->disableOriginalConstructor()->getMock();
        $this->buildService = $this->getMockBuilder(BuildService::class)->disableOriginalConstructor()->getMock();
        $this->applicationFinder = $this->getMockBuilder(FinderInterface::class)->getMock();
        $this->serverFinder = $this->getMockBuilder(FinderInterface::class)->getMock();
        $this->buildFinder = $this->getMockBuilder(FinderInterface::class)->getMock();
        $this->applicationService = $this->getMockBuilder(ApplicationService::class)->disableOriginalConstructor()->getMock();
        $this->settingsService = $this->getMockBuilder(SettingsService::class)->disableOriginalConstructor()->getMock();
        $this->serverService = $this->getMockBuilder(ServerService::class)->disableOriginalConstructor()->getMock();
        $this->app1Id = mt_rand(1, 100);
        $this->app1 = $this->getMockBuilder(ApplicationEntity::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getId', 'getName'))
            ->getMock();
        $this->app2Id = mt_rand(1, 100);
        $this->app2 = $this->getMockBuilder(ApplicationEntity::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getId', 'getName'))
            ->getMock();
        $application = new Application();
        $application->add(new ProvisionCommand());
        $this->command = $application->find('digip:provision');
        $this->command->setContainer($this->container);
        $this->tester = new CommandTester($this->command);
        $this->buildId = mt_rand(1, 100);
    }

    public function testProvisionInvalidAppChoice()
    {
        $this->provisionApplicationNoArgsTest(false);
    }

    public function testProvisionValidAppChoice()
    {
        $this->provisionApplicationNoArgsTest();
    }

    public function testProvisionAppArgument()
    {
        $this->buildMocks();
        $this->applicationService
            ->expects($this->once())
            ->method('getFinder')
            ->willReturn($this->applicationFinder);
        $this->applicationFinder
            ->expects($this->at(0))
            ->method('get')
            ->with($this->app1Id)
            ->willReturn($this->app1);
        $this->tester->execute(
            array('command' => $this->command->getName(), 'application' => $this->app1Id)
        );
    }

    public function testProvisionBuildOption()
    {
        $this->provisionBuildOptionTest();
    }

    public function testProvisionStartedBuildOption()
    {
        $this->provisionBuildOptionTest(true);
    }

    /**
     * @dataProvider appOptionsProvider
     */
    public function testProvisionAppOptions($options)
    {
        $expectedMask = 0;
        $commandOptions = array();
        foreach ($options as $option => $mask)
        {
            $expectedMask |= $mask;
            $commandOptions[$option] = true;
        }
        $this->buildMocks(true, $expectedMask);
        $this->applicationService
            ->expects($this->once())
            ->method('getFinder')
            ->willReturn($this->applicationFinder);
        $this->applicationFinder
            ->expects($this->any())
            ->method('get')
            ->with($this->app1Id)
            ->willReturn($this->app1);
        $this->tester->execute(
            array_merge(
                array('command' => $this->command->getName(), 'application' => $this->app1Id), $commandOptions
            )
        );
    }

    public function appOptionsProvider()
    {
        $options = array(
            '--ci' => BuildService::PROVISION_CI,
            '--ci-override' => BuildService::PROVISION_CI_OVERRIDE,
            '--filesystem' => BuildService::PROVISION_FILESYSTEM,
            '--config' => BuildService::PROVISION_CONFIG_FILES,
            '--sock' => BuildService::PROVISION_SOCK,
            '--cron' => BuildService::PROVISION_CRON,
            '--all' => BuildService::PROVISION_ALL,
        );
        $data = array();
        // Test each option individually
        foreach ($options as $name => $mask)
        {
            $data[] = array(array($name => $mask));
        }

        // Test random combinations.
        for ($i = 0; $i < 10; $i++)
        {
            $randomOptions = array_rand($options, mt_rand(2, count($options)));
            $keys = array_combine($randomOptions, $randomOptions);
            $data[] = array(array_intersect_key($options, $keys));
        }
        return $data;
    }

    protected function provisionApplicationNoArgsTest($validChoice = true)
    {
        $this->buildMocks();
        $this->applicationService
            ->expects($this->once())
            ->method('getFinder')
            ->willReturn($this->applicationFinder);
        $this->app1
            ->expects($this->once())
            ->method('getId')
            ->willReturn($this->app1Id);
        $this->app1
            ->expects($this->any())
            ->method('getName')
            ->willReturn('App1');

        $this->app2
            ->expects($this->once())
            ->method('getId')
            ->willReturn($this->app2Id);
        $this->app2
            ->expects($this->any())
            ->method('getName')
            ->willReturn('App2');
        $all = [
            $this->app1,
            $this->app2,
        ];
        $applciationResult = $this->getMockBuilder(ResultInterface::class)->getMock();
        $applciationResult
            ->expects($this->at(0))
            ->method('getAll')
            ->willReturn($all);

        $this->applicationFinder
            ->expects($this->at(0))
            ->method('find')
            ->willReturn($applciationResult);
        $this->tester->setInputs($validChoice ? array('App1') : array('qsdf', 'App1'));
        $this->tester->execute(
            array('command' => $this->command->getName())
        );
        $output = $this->tester->getDisplay();
        $this->assertContains('Which application would you like to deploy?', $output);
        $this->assertContains('[' . str_pad($this->app1Id, 2, ' ', STR_PAD_RIGHT) . '] App1', $output);
        $this->assertContains('[' . str_pad($this->app2Id, 2, ' ', STR_PAD_RIGHT) . '] App2', $output);
        if (!$validChoice)
        {
            $this->assertContains('Value "qsdf" is invalid', $output);
        }
    }

    protected function provisionBuildOptionTest($started = false)
    {
        $this->buildMocks(!$started);
        $build = $this->getMockBuilder(Build::class)->disableOriginalConstructor()->getMock();
        $build->expects($this->once())->method('isStarted')->willReturn($started);
        if ($started)
        {
            $build->expects($this->once())->method('getId')->willReturn($this->buildId);
        }
        else
        {
            $build->expects($this->once())->method('getApplication')->willReturn($this->app1);
            $build->expects($this->once())->method('getType')->willReturn(Build::TYPE_PROVISION);
        }
        $this->buildService
            ->expects($this->once())
            ->method('getFinder')
            ->willReturn($this->buildFinder);
        $this->buildFinder
            ->expects($this->at(0))
            ->method('get')
            ->with($this->buildId)
            ->willReturn($build);
        try
        {
            $this->tester->execute(
                array('command' => $this->command->getName(), '--build' => $this->buildId)
            );
            if ($started) {
                $this->fail('Should not be able to load a build that has already started.');
            }
        }
        catch (\Exception $e)
        {
            if ($started)
            {
                $this->assertInstanceOf(\InvalidArgumentException::class, $e);
                $this->assertEquals($e->getMessage(), sprintf(
                        'build %s was already started', $this->buildId
                ));
            }
            else
            {
                $this->fail($e->getMessage());
                throw $e;
            }
        }
    }

    protected function buildMocks($willRun = true, $expectedMask = BuildService::PROVISION_ALL)
    {
        $this->container
            ->expects($this->at(0))
            ->method('get')
            ->with('digip_deploy.entity.build')
            ->willReturn($this->buildService);

        $this->container
            ->expects($this->at(1))
            ->method('get')
            ->with('digip_deploy.entity.application')
            ->willReturn($this->applicationService);

        $this->container
            ->expects($this->at(2))
            ->method('get')
            ->with('digip_deploy.entity.settings')
            ->willReturn($this->settingsService);

        $this->container
            ->expects($this->at(3))
            ->method('get')
            ->with('digip_deploy.entity.server')
            ->willReturn($this->serverService);

        if ($willRun)
        {
            $settings = $this->getMockBuilder(Settings::class)->getMock();
            $this->settingsService
                ->expects($this->once())
                ->method('getSettings')
                ->willReturn($settings);
            $this->serverService
                ->expects($this->once())
                ->method('getFinder')
                ->willReturn($this->serverFinder);

            $serverResult = $this->getMockBuilder(ResultInterface::class)->getMock();
            $serverResult
                ->expects($this->at(0))
                ->method('getAll')
                ->willReturn(array());

            $this->serverFinder
                ->expects($this->at(0))
                ->method('find')
                ->willReturn($serverResult);

            $this->buildService
                ->expects($this->once())
                ->method('execute')
                ->with(
                    $this->callback(function(Build $build)
                    {
                        return $build->getApplication() === $this->app1 && $build->getType() === Build::TYPE_PROVISION;
                    }), array(), $settings, $expectedMask
                )
                ->willReturn(true);
        }
    }

}
