<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Command;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Repository\TaskRepository;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class AbstractCommandTest extends TestCase
{

    protected function getContainerMock($entityManager, $eventDispatcher)
    {
        $mock = $this
            ->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('doctrine.orm.default_entity_manager'))
            ->willReturn($entityManager);

        $mock
            ->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('event_dispatcher'))
            ->willReturn($eventDispatcher);

        return $mock;
    }

    protected function getInputInterfaceMock()
    {
        $mock = $this
            ->getMockBuilder(InputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    protected function getOutputInterfaceMock()
    {
        $mock = $this
            ->getMockBuilder(OutputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    protected function getEntityManagerMock($taskRepository)
    {
        $mock = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo(Task::class))
            ->willReturn($taskRepository);

        return $mock;
    }

    protected function getEventDispatcherMock()
    {
        $mock = $this
            ->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    protected function getTaskRepositoryMock($type, $task)
    {
        $mock = $this
            ->getMockBuilder(TaskRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->at(0))
            ->method('getNextTask')
            ->with($this->equalTo($type))
            ->willReturn($task);

        return $mock;
    }
}

