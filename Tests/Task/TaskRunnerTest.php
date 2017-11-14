<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Task;

use DigipolisGent\Domainator9k\CoreBundle\Task\TaskInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskResult;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskRunner;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of TaskRunnerTest
 *
 * @author Jelle Sebreghts
 */
class TaskRunnerTest extends TestCase
{
    use DataGenerator;

    public function testGetSetTasks() {
        $runner = new TaskRunner();
        $tasks = [$this->getMockBuilder(TaskInterface::class)->getMock()];
        $this->assertEquals($runner, $runner->setTasks($tasks));
        $this->assertEquals($tasks, $runner->getTasks());
        $extraTask = $this->getMockBuilder(TaskInterface::class)->getMock();
        $this->assertEquals($runner, $runner->addTask($extraTask));
        $tasks[] = $extraTask;
        $this->assertEquals($tasks, $runner->getTasks());
        $runner->clearTasks();
        $this->assertEmpty($runner->getTasks());
    }

    public function testRun() {
        $runner = new TaskRunner();
        $result = $this->getMockBuilder(TaskResult::class)->getMock();
        $result->expects($this->once())->method('isSuccess')->willReturn(true);
        $tasks = [(new Stub\StubTask())->setExecuteResult($result)];
        $runner->setTasks($tasks);
        $taskResult = $runner->run();
        $this->assertInstanceOf(TaskResult::class, $taskResult);
        $this->assertTrue($taskResult->isSuccess());
    }

    public function testRunFails() {
        $runner = new TaskRunner();
        $result = $this->getMockBuilder(TaskResult::class)->getMock();
        $result->expects($this->exactly(2))->method('isSuccess')->willReturnOnConsecutiveCalls(true, false);
        $result->expects($this->any())->method('getMessages')->willReturn([$this->getAlphaNumeric()]);
        $tasks = [
            (new Stub\StubTask())->setExecuteResult($result)->setIsExecuted(true)->setRevertResult(true),
            (new Stub\StubTask())->setExecuteResult($result)->setIsExecuted(true)->setRevertResult(true)
        ];
        $runner->setTasks($tasks);
        $taskResult = $runner->run();
        $this->assertInstanceOf(TaskResult::class, $taskResult);
        $this->assertFalse($taskResult->isSuccess());
    }
}
