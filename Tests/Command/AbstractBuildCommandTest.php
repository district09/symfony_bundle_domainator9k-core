<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Command;

use Ctrl\Common\EntityService\Finder\FinderInterface;
use Ctrl\Common\EntityService\Finder\ResultInterface;
use DigipolisGent\Domainator9k\CoreBundle\Command\AbstractBuildCommand;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\EntityService\ApplicationService;
use DigipolisGent\Domainator9k\CoreBundle\EntityService\BuildService;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of AbstractBuildCommandTest
 *
 * @author Jelle Sebreghts
 */
class AbstractBuildCommandTest extends TestCase
{

    /**
     * An AbstractBuildCommand to test.
     *
     * @var AbstractBuildCommand|PHPUnit_Framework_MockObject_MockObject
     */
    protected $buildCommand;

    /**
     * The mocked container.
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    /**
     * The mocked build.
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $build;

    /**
     * The mocked build service.
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $buildService;

    /**
     * The mocked finder.
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $finder;

    /**
     * The mocked application service.
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $applicationService;

    protected function setUp()
    {
        parent::setUp();
        $this->buildCommand = $this->getMockForAbstractClass(AbstractBuildCommand::class);
        $this->container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $this->buildCommand->setContainer($this->container);
        $this->build = $this->getMockBuilder(Build::class)->disableOriginalConstructor()->getMock();
        $this->buildService = $this->getMockBuilder(BuildService::class)->disableOriginalConstructor()->getMock();
        $this->finder = $this->getMockBuilder(FinderInterface::class)->getMock();
        $this->applicationService = $this->getMockBuilder(ApplicationService::class)->disableOriginalConstructor()->getMock();
    }

    public function testLoadBuild()
    {
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('digip_deploy.entity.build')
            ->willReturn($this->buildService);

        $this->buildService
            ->expects($this->once())
            ->method('getFinder')
            ->willReturn($this->finder);

        $buildId = time();

        $this->finder
            ->expects($this->once())
            ->method('get')
            ->with($buildId)
            ->willReturn($this->build);

        $this->build
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(false);

        $this->assertEquals($this->build, $this->buildCommand->loadBuild($buildId));
    }

    public function testLoadStartedBuild()
    {
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('digip_deploy.entity.build')
            ->willReturn($this->buildService);

        $this->buildService
            ->expects($this->once())
            ->method('getFinder')
            ->willReturn($this->finder);

        $buildId = time();

        $this->finder
            ->expects($this->once())
            ->method('get')
            ->with($buildId)
            ->willReturn($this->build);

        $this->build
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(true);

        $this->build
            ->expects($this->once())
            ->method('getId')
            ->willReturn($buildId);

        try
        {
            $this->buildCommand->loadBuild($buildId);
            $this->fail('No InalidArgumentException thrown when loading a build that has already started.');
        }
        catch (Exception $e)
        {
            $this->assertInstanceOf(InvalidArgumentException::class, $e);
            $this->assertEquals(
                sprintf(
                    'build %s was already started', $buildId
                ), $e->getMessage()
            );
        }
    }

    public function testGetApplications()
    {
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('digip_deploy.entity.application')
            ->willReturn($this->applicationService);

        $this->applicationService
            ->expects($this->once())
            ->method('getFinder')
            ->willReturn($this->finder);

        $result = $this->getMockBuilder(ResultInterface::class)->getMock();

        $this->finder
            ->expects($this->once())
            ->method('find')
            ->willReturn($result);

        $all = [
            mt_rand(0, 100),
            mt_rand(0, 100),
            mt_rand(0, 100),
        ];
        $result
            ->expects($this->once())
            ->method('getAll')
            ->willReturn($all);

        $this->assertEquals($all, $this->buildCommand->getApplications());

        // Call it again to ensure all methods are only called once and the
        // results are properly cached in the property.
        $this->assertEquals($all, $this->buildCommand->getApplications());
    }

    public function testLoadApplication()
    {
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('digip_deploy.entity.application')
            ->willReturn($this->applicationService);

        $this->applicationService
            ->expects($this->once())
            ->method('getFinder')
            ->willReturn($this->finder);

        $application = time();
        $applicationId = mt_rand(0, 100);

        $this->finder
            ->expects($this->once())
            ->method('get')
            ->with($applicationId)
            ->willReturn($application);

        $this->assertEquals($application, $this->buildCommand->loadApplication($applicationId));
    }

}
