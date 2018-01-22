<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Command;

use DigipolisGent\Domainator9k\CoreBundle\Command\BuildCommand;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;

class BuildCommandTest extends AbstractCommandTest
{

    public function testExecuteWithNoTask()
    {
        $taskRepository = $this->getTaskRepositoryMock(Task::TYPE_BUILD, null);

        $entityManager = $this->getEntityManagerMock($taskRepository);
        $eventDispatcher = $this->getEventDispatcherMock();

        $container = $this->getContainerMock($entityManager, $eventDispatcher);
        $inputInterface = $this->getInputInterfaceMock();
        $outputInterface = $this->getOutputInterfaceMock();

        $buildCommand = new BuildCommand();
        $buildCommand->setContainer($container);
        $buildCommand->execute($inputInterface, $outputInterface);
    }

    public function testExecuteWithTask()
    {
        $taskRepository = $this->getTaskRepositoryMock(Task::TYPE_BUILD, new Task());

        $entityManager = $this->getEntityManagerMock($taskRepository);
        $eventDispatcher = $this->getEventDispatcherMock();

        $container = $this->getContainerMock($entityManager, $eventDispatcher);
        $inputInterface = $this->getInputInterfaceMock();
        $outputInterface = $this->getOutputInterfaceMock();

        $buildCommand = new BuildCommand();
        $buildCommand->setContainer($container);
        $buildCommand->execute($inputInterface, $outputInterface);
    }
}
