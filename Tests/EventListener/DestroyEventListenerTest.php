<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\EventListener;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Event\BuildEvent;
use DigipolisGent\Domainator9k\CoreBundle\Event\DestroyEvent;
use DigipolisGent\Domainator9k\CoreBundle\EventListener\BuildEventListener;
use DigipolisGent\Domainator9k\CoreBundle\EventListener\DestroyEventListener;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskLoggerService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DestroyEventListenerTest extends TestCase
{

    public function testOnStart(){
        $taskLoggerService = $this->getTaskLoggerServiceMock();
        $taskLoggerService
            ->expects($this->at(0))
            ->method('setTask');

        $entityManager = $this->getEntityManagerMock();

        $buildEventListener = new DestroyEventListener($taskLoggerService,$entityManager);
        $buildEventListener->onStart(new DestroyEvent(new Task()));
    }

    public function testOnEnd(){
        $taskLoggerService = $this->getTaskLoggerServiceMock();
        $entityManager = $this->getEntityManagerMock();

        $buildEventListener = new DestroyEventListener($taskLoggerService,$entityManager);
        $buildEventListener->onEnd(new DestroyEvent(new Task()));
    }

    public function getTaskLoggerServiceMock(){
        $mock = $this
            ->getMockBuilder(TaskLoggerService::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    public function getEntityManagerMock(){
        $mock = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->at(0))
            ->method('persist');

        $mock
            ->expects($this->at(1))
            ->method('flush');

        return $mock;
    }

}