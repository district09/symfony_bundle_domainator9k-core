<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Repository\TaskRepository;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Service\ProvisionService;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaskServiceTest extends TestCase
{

    /**
     * @var Task
     */
    protected $task;

    /**
     * @var EntityManagerInterface|MockObject
     */
    protected $entityManager;

    /**
     * @var ProvisionService|MockObject
     */
    protected $provisionService;

    /**
     * @var TaskService
     */
    protected $taskService;

    /**
     * @var int
     */
    protected $entityManagerIndex;

    /**
     * @var int
     */
    protected $provisionServiceIndex;

    protected function setUp()
    {
        parent::setUp();
        $this->task = new Task();
        $this->task->setType(Task::TYPE_BUILD);
        $id = uniqid();
        $prop = new \ReflectionProperty($this->task, 'id');
        $prop->setAccessible(true);
        $prop->setValue($this->task, $id);

        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMock();

        $this->provisionService = $this->getMockBuilder(ProvisionService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManagerIndex = 0;
        $this->provisionServiceIndex = 0;

        $this->taskService = new TaskService($this->entityManager, $this->provisionService);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /Task "(.*)" cannot be restarted\./
     */
    public function testRunNotNew()
    {
        $this->task->setProcessed();
        $this->taskService->run($this->task);
    }

    public function testRunSuccess()
    {
        $this->expectSuccessfulRun();

        $this->taskService->run($this->task);
    }

    public function testRunFailed()
    {
        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('persist')
            ->with($this->callback(
                function (Task $task)
                {
                    return $task->isInProgress();
                }
            ));

        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('flush');

        $this->provisionService
            ->expects($this->at($this->provisionServiceIndex++))
            ->method('run')
            ->with($this->callback(
                function (Task $task) {
                    return $task->isInProgress();
                }
            ))
            ->willReturnCallback(function (Task $task) {
                $task->setFailed();
            });

        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('persist')
            ->with($this->callback(
                function (Task $task) {
                    return $task->isFailed();
                }
            ));

        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('flush');

        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('persist')
            ->with($this->callback(
                function (Task $task) {
                    return $task->isFailed() && (strpos($task->getLog(), 'Task run failed.') !== false);
                }
            ));

        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('flush');

        $this->taskService->run($this->task);
    }

    public function testRunCancelled()
    {
        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('persist')
            ->with($this->callback(
                function (Task $task)
                {
                    return $task->isInProgress();
                }
            ));

        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('flush');

        $this->provisionService
            ->expects($this->at($this->provisionServiceIndex++))
            ->method('run')
            ->with($this->callback(
                function (Task $task) {
                    return $task->isInProgress();
                }
            ))
            ->willReturnCallback(function (Task $task) {
                $task->setCancelled();
            });

        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('persist')
            ->with($this->callback(
                function (Task $task) {
                    return $task->isCancelled() && is_null($task->getLog());
                }
            ));

        $this->taskService->run($this->task);
    }

    public function testRunNext()
    {
        $repository = $this->getMockBuilder(TaskRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->at(0))
            ->method('getNextTask')
            ->with(Task::TYPE_BUILD)
            ->willReturn($this->task);
        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('getRepository')
            ->with(Task::class)
            ->willReturn($repository);
        $this->expectSuccessfulRun();
        $this->taskService->runNext(Task::TYPE_BUILD);
    }

    public function testCancel()
    {
        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('persist')
            ->with($this->callback(
                function (Task $task)
                {
                    return $task->isCancelled() && (strpos($task->getLog(), 'Task run cancelled.') !== false);
                }
            ));

        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('flush');

        $this->taskService->cancel($this->task);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /Task (.*) cannot be cancelled\./
     */
    public function testCancelRunning()
    {
        $this->task->setInProgress();
        $this->taskService->cancel($this->task);
    }

    protected function expectSuccessfulRun()
    {
        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('persist')
            ->with($this->callback(
                function (Task $task)
                {
                    return $task->isInProgress();
                }
            ));

        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('flush');

        $this->provisionService
            ->expects($this->at($this->provisionServiceIndex++))
            ->method('run')
            ->with($this->callback(
                function (Task $task) {
                    return $task->isInProgress();
                }
            ));

        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('persist')
            ->with($this->callback(
                function (Task $task) {
                    return $task->isProcessed();
                }
            ));

        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('flush');

        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('persist')
            ->with($this->callback(
                function (Task $task) {
                    return $task->isProcessed() && (strpos($task->getLog(), 'Task run completed.') !== FALSE);
                }
            ));

        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('flush');
    }
}
