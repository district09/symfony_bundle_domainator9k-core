<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

class TaskRunner implements TaskRunnerInterface
{
    /**
     * @var array|TaskInterface[]
     */
    protected $tasks = array();

    /**
     * @var bool
     */
    protected $revertOnFailure = true;

    /**
     * @return array
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param TaskInterface[] $tasks
     *
     * @return $this
     */
    public function setTasks(array $tasks)
    {
        $this->tasks = $tasks;

        return $this;
    }

    /**
     * @param TaskInterface $task
     *
     * @return $this
     */
    public function addTask(TaskInterface $task)
    {
        $this->tasks[] = $task;

        return $this;
    }

    /**
     * @return $this
     */
    public function clearTasks()
    {
        $this->tasks = array();

        return $this;
    }

    /**
     * @return TaskResult
     */
    public function run()
    {
        $executedTasks = array();
        $log = array();
        $failed = false;

        foreach ($this->tasks as $task) {
            Messenger::send('executing task: '.$task->getName());
            $result = $task->execute();
            $log[] = array(
                'task' => $task,
                'result' => $result,
            );
            if (!$result->isSuccess()) {
                $failed = true;
                Messenger::send('task failed: '.$task->getName());
                Messenger::send($result->getMessages());
                break;
            }
            $executedTasks[] = $task;
        }

        $runResult = new TaskResult();
        $runResult->setData($log);

        if ($failed) {
            $runResult->setSuccess(false);
            $this->revert($executedTasks);
        }

        return $runResult;
    }

    /**
     * @param array|TaskInterface[] $tasks
     */
    public function revert(array $tasks)
    {
        // Revert in reverse order of execution.
        foreach (array_reverse($tasks) as $task) {
            // Only revert executed tasks.
            if ($task->isExecuted()) {
                $task->revert();
            }
        }
    }
}
