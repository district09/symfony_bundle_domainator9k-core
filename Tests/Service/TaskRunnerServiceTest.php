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

    protected function setUp(): void
    {
        parent::setUp();
        $this->task = new Task();
        $this->task->setType(Task::TYPE_BUILD);
        $id = random_int(1, 9999);
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
    }

    public function testRunNotNew()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Task "(.*)" cannot be restarted\./');
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
            ->expects($this->atLeastOnce())
            ->method('persist')
            ->with($this->callback(
                function (Task $task)
                {
                    return $task->isInProgress();
                }
            ));

        $this->entityManager
            ->expects($this->atLeastOnce())
            ->method('flush');

        $this->logger
            ->expects($this->any())
            ->method('addLogMessage')
            ->with($this->task, '', '', 0);

        $this->logger
            ->expects($this->any())
            ->method('addFailedLogMessage')
            ->with($this->task, 'Task run failed.', 0);

        $buildProvisioners = [];
        foreach (range(0, 3) as $i) {
            $mock = $this->getMockBuilder(ProvisionerInterface::class)
                ->getMock();
            $mock->expects($this->once())
                ->method('setTask')
                ->with($this->task)
                ->willReturn(null);
            $mock->expects($this->once())
                ->method('run')
                ->willReturn(null);
            $buildProvisioners[] = $mock;
        }
        $mock = $this->getMockBuilder(ProvisionerInterface::class)
            ->getMock();
        $mock->expects($this->once())
            ->method('setTask')
            ->with($this->task);
        $mock->expects($this->once())
            ->method('run')
            ->willReturnCallback(function () {
                $this->task->setFailed();
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
            ->expects($this->any())
            ->method('persist')
            ->withConsecutive(
                [
                    $this->callback(
                        function (Task $task)
                        {
                            return $task->isInProgress();
                        }
                    )
                ],
                [
                    $this->callback(
                        function (Task $task)
                        {
                            return $task->isCancelled();
                        }
                    )
                ]
            );

        $this->entityManager
            ->expects($this->atLeast(2))
            ->method('flush');

        $buildProvisioners = [];
        foreach (range(0, 5) as $i) {
            $mock = $this->getMockBuilder(ProvisionerInterface::class)
                ->getMock();
            $mock->expects($this->once())
                ->method('setTask')
                ->with($this->task)
                ->willReturn(null);
            $mock->expects($this->once())
                ->method('run')
                ->willReturn(null);
            $buildProvisioners[] = $mock;
        }

        $mock = $this->getMockBuilder(ProvisionerInterface::class)
                ->getMock();
            $mock->expects($this->once())
                ->method('setTask')
                ->with($this->task)
                ->willReturn(null);
            $mock->expects($this->once())
                ->method('run')
                ->willReturnCallback(
                    function ()
                    {
                        $this->task->setCancelled();
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
        $repository->expects($this->any())
            ->method('getNextTask')
            ->with(Task::TYPE_BUILD)
            ->willReturn($this->task);
        $this->entityManager
            ->expects($this->atLeastOnce())
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
            ->expects($this->any())
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

    public function testCancelRunning()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Task (.*) cannot be cancelled\./');
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
            ->expects($this->atLeastOnce())
            ->method('persist')
            ->with($this->callback(
                function (Task $task)
                {
                    return $task->isInProgress();
                }
            ));

        $this->entityManager
            ->expects($this->atLeastOnce())
            ->method('flush');

        $this->logger
            ->expects($this->any())
            ->method('addLogMessage')
            ->with($this->task, '', '', 0);

        $this->logger
            ->expects($this->any())
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
                        ->method('setTask')
                        ->with($this->task)
                        ->willReturn(null);
                    $mock->expects($this->once())
                        ->method('run')
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
                        ->method('setTask')
                        ->with($this->task)
                        ->willReturn(null);
                    $mock->expects($this->once())
                        ->method('run')
                        ->with()
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
