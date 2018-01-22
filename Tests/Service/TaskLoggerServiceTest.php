<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskLoggerService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TaskLoggerServiceTest extends TestCase
{

    public function testAddLine(){
        $entityManager = $this->getEntityManagerMock();
        $loggerService = new TaskLoggerService($entityManager);
        $task = new Task();
        $loggerService->setTask($task);
        $loggerService->addLine('New log line');
    }

    private function getEntityManagerMock(){
        $mock = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->at(0))
            ->method('persist');

        $mock
            ->expects($this->at(0))
            ->method('flush');

        return $mock;
    }

}
