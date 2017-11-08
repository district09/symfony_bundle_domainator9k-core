<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\DependencyInjection\Compiler;

use DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler\TaskPass;
use DigipolisGent\Domainator9k\CoreBundle\Task\Provision\Filesystem;
use InvalidArgumentException;
use Symfony\Bundle\WebProfilerBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Description of TaskPassTest
 *
 * @author Jelle Sebreghts
 */
class TaskPassTest extends TestCase
{

    public function testNoCiTaskFactory()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $container
            ->expects($this->once())
            ->method('has')
            ->with('digip_deploy.task_factory')
            ->willReturn(false);

        // Assert no other methods are called.
        $container
            ->expects($this->never())
            ->method('findDefinition')
            ->with('digip_deploy.task_factory');

        $container
            ->expects($this->never())
            ->method('findTaggedServiceIds')
            ->with('digip_deploy.task');

        $this->process($container);
    }

    public function testTaggedServices()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $taskFactoryDefinition = $this->getMockBuilder(Definition::class)->getMock();
        $taskDefinition = $this->getMockBuilder(Definition::class)->getMock();

        $container
            ->expects($this->at(0))
            ->method('has')
            ->with('digip_deploy.task_factory')
            ->willReturn(true);

        $container
            ->expects($this->at(1))
            ->method('findDefinition')
            ->with('digip_deploy.task_factory')
            ->willReturn($taskFactoryDefinition);

        $container
            ->expects($this->at(3))
            ->method('findDefinition')
            ->with('digip_deploy.provision_filesystem_task')
            ->willReturn($taskDefinition);

        $taskDefinition->expects($this->once())->method('getClass')->willReturn(Filesystem::class);

        $taskFactoryDefinition->expects($this->once())->method('addMethodCall')->with('addTaskDefinition', [Filesystem::class]);

        $container
            ->expects($this->at(2))
            ->method('findTaggedServiceIds')
            ->with('digip_deploy.task')
            ->willReturn(['digip_deploy.provision_filesystem_task' => ['digip_deploy.task']]);

        $this->process($container);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testTaggedServicesInvalid()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $taskFactoryDefinition = $this->getMockBuilder(Definition::class)->getMock();
        $taskDefinition = $this->getMockBuilder(Definition::class)->getMock();

        $container
            ->expects($this->at(0))
            ->method('has')
            ->with('digip_deploy.task_factory')
            ->willReturn(true);

        $container
            ->expects($this->at(1))
            ->method('findDefinition')
            ->with('digip_deploy.task_factory')
            ->willReturn($taskFactoryDefinition);

        $container
            ->expects($this->at(3))
            ->method('findDefinition')
            ->with('digip_deploy.provision_filesystem_task')
            ->willReturn($taskDefinition);

        $taskDefinition->expects($this->once())->method('getClass')->willReturn('\stdClass');

        $taskFactoryDefinition->expects($this->never())->method('addMethodCall')->with('addTaskDefinition', ['\stdClass']);

        $container
            ->expects($this->at(2))
            ->method('findTaggedServiceIds')
            ->with('digip_deploy.task')
            ->willReturn(['digip_deploy.provision_filesystem_task' => ['digip_deploy.task']]);

        $this->process($container);
    }

    protected function process(ContainerBuilder $container)
    {
        $pass = new TaskPass();
        $pass->process($container);
    }

}
