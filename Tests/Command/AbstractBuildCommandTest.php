<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Command;

use DigipolisGent\Domainator9k\CoreBundle\Command\AbstractBuildCommand;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\EntityService\BuildService;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of AbstractBuildCommandTest
 *
 * @author Jelle Sebreghts
 */
class AbstractBuildCommandTest extends TestCase {

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

  protected function setUp() {
    parent::setUp();
    $this->buildCommand = $this->getMockForAbstractClass(AbstractBuildCommand::class);
    $this->container = $this->getMockBuilder(ContainerInterface::class)->getMock();
    $this->buildCommand->setContainer($this->container);
    $this->build = $this->getMock(Build::class)->getMock();
    $this->buildService = $this->getMockBuilder(BuildService::class)->getMock();
    $this->finder = $this->getMockBuilder()
  }

  public function testLoadBuild() {
    $this->container
      ->expects($this->once())
      ->method('get')
      ->with('digip_deploy.entity.build')
      ->willReturn($this->buildService);

    $this->buildService->expects($this->once())->method('getFinder')->willReturn($this->finder);
  }


}
