<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Repository\TaskRepository;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Service\ProvisionService;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskLoggerService;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskRunnerService;
use DigipolisGent\Domainator9k\CoreBundle\Provisioner\ProvisionerInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaskRunnerServiceTest extends TestCase
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
     * @var TaskLoggerService
     */
    protected $logger;

    /**
     * @var TaskRunnerService
     */
    protected $taskRunnerService;

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

        $this->logger = $this->getMockBuilder(TaskLoggerService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManagerIndex = 0;
        $this->provisionServiceIndex = 0;
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /Task "(.*)" cannot be restarted\./
     */
    public function testRunNotNew()
    {
        $this->task->setProcessed();
        $this->taskRunnerService = new TaskRunnerService([], [], $this->entityManager, $this->logger);
        $this->taskRunnerService->run($this->task);
    }

    public function testRunSuccessBuild()
    {
        $this->expectSuccessfulRun();
        $result = $this->taskRunnerService->run($this->task);
        $this->assertTrue($result);
        $this->assertTrue($this->task->isProcessed());
    }

    public function testRunSuccessDestroy()
    {
        $this->task->setType(Task::TYPE_DESTROY);
        $this->expectSuccessfulRun();
        $result = $this->taskRunnerService->run($this->task);
        $this->assertTrue($result);
        $this->assertTrue($this->task->isProcessed());
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

        $this->logger
            ->expects($this->at(0))
            ->method('addLogMessage')
            ->with($this->task, '', '', 0);

        $this->logger
            ->expects($this->at(1))
            ->method('addFailedLogMessage')
            ->with($this->task, 'Task run failed.', 0);

        $buildProvisioners = [];
        foreach (range(0, 3) as $i) {
            $mock = $this->getMockBuilder(ProvisionerInterface::class)
                ->getMock();
            $mock->expects($this->once())
                ->method('run')
                ->with($this->task)
                ->willReturn(null);
            $buildProvisioners[] = $mock;
        }
        $mock = $this->getMockBuilder(ProvisionerInterface::class)
            ->getMock();
        $mock->expects($this->once())
            ->method('run')
            ->with($this->task)
            ->willReturnCallback(function (Task $task) {
                $task->setFailed();
            });
        $buildProvisioners[] = $mock;

        foreach (range(0, 2) as $i) {
            $mock = $this->getMockBuilder(ProvisionerInterface::class)
                ->getMock();
            $mock->expects($this->never())
                ->method('run');
            $buildProvisioners[] = $mock;
        }

        $this->taskRunnerService = new TaskRunnerService(
            $buildProvisioners,
            [],
            $this->entityManager,
            $this->logger
        );

        $result = $this->taskRunnerService->run($this->task);
        $this->assertFalse($result);
        $this->assertTrue($this->task->isFailed());
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

        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('persist')
            ->with($this->callback(
                function (Task $task)
                {
                    return $task->isCancelled();
                }
            ));

        $this->entityManager
            ->expects($this->at($this->entityManagerIndex++))
            ->method('flush');

        $buildProvisioners = [];
        foreach (range(0, 5) as $i) {
            $mock = $this->getMockBuilder(ProvisionerInterface::class)
                ->getMock();
            $mock->expects($this->once())
                ->method('run')
                ->with($this->task)
                ->willReturn(null);
            $buildProvisioners[] = $mock;
        }

        $mock = $this->getMockBuilder(ProvisionerInterface::class)
                ->getMock();
            $mock->expects($this->once())
                ->method('run')
                ->with($this->task)
                ->willReturnCallback(
                    function (Task $task)
                    {
                        $task->setCancelled();
                    }
                );
            $buildProvisioners[] = $mock;

        $this->taskRunnerService = new TaskRunnerService(
            $buildProvisioners,
            [],
            $this->entityManager,
            $this->logger
        );
        $result = $this->taskRunnerService->run($this->task);
        $this->assertFalse($result);
        $this->assertTrue($this->task->isCancelled());
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
        $result = $this->taskRunnerService->runNext(Task::TYPE_BUILD);
        $this->assertTrue($result);
        $this->assertTrue($this->task->isProcessed());
    }

    public function testCancel()
    {
        $this->logger
            ->expects($this->at(0))
            ->method('addInfoLogMessage')
            ->with($this->task, 'Task run cancelled.');
        $this->taskRunnerService = new TaskRunnerService(
            [],
            [],
            $this->entityManager,
            $this->logger
        );
        $this->taskRunnerService->cancel($this->task);

        $this->assertTrue($this->task->isCancelled());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /Task (.*) cannot be cancelled\./
     */
    public function testCancelRunning()
    {
        $this->task->setInProgress();
        $this->taskRunnerService = new TaskRunnerService(
            [],
            [],
            $this->entityManager,
            $this->logger
        );
        $this->taskRunnerService->cancel($this->task);
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

        $this->logger
            ->expects($this->at(0))
            ->method('addLogMessage')
            ->with($this->task, '', '', 0);

        $this->logger
            ->expects($this->at(1))
            ->method('addSuccessLogMessage')
            ->with($this->task, 'Task run completed.', 0);

        $buildProvisioners = [];
        $destroyProvisioners = [];
        switch ($this->task->getType()) {
            case Task::TYPE_BUILD:
                foreach (range(0, 5) as $i) {
                    $mock = $this->getMockBuilder(ProvisionerInterface::class)
                        ->getMock();
                    $mock->expects($this->once())
                        ->method('run')
                        ->with($this->task)
                        ->willReturn(null);
                    $buildProvisioners[] = $mock;
                }

                foreach (range(0, 5) as $i) {
                    $mock = $this->getMockBuilder(ProvisionerInterface::class)
                        ->getMock();
                    $mock->expects($this->never())
                        ->method('run');
                    $destroyProvisioners[] = $mock;
                }
                break;
            case Task::TYPE_DESTROY:

                $buildProvisioners = [];
                foreach (range(0, 5) as $i) {
                    $mock = $this->getMockBuilder(ProvisionerInterface::class)
                        ->getMock();
                    $mock->expects($this->never())
                        ->method('run');
                    $buildProvisioners[] = $mock;
                }

                $destroyProvisioners = [];
                foreach (range(0, 5) as $i) {
                    $mock = $this->getMockBuilder(ProvisionerInterface::class)
                        ->getMock();
                    $mock->expects($this->once())
                        ->method('run')
                        ->with($this->task)
                        ->willReturn(null);
                    $destroyProvisioners[] = $mock;
                }
                break;
        }

        $this->taskRunnerService = new TaskRunnerService(
            $buildProvisioners,
            $destroyProvisioners,
            $this->entityManager,
            $this->logger
        );
    }
}
