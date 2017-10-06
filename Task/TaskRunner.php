<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

class TaskRunner
{
    /**
     * @var array|AbstractTask[]
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
     * @param array $tasks
     *
     * @return $this
     */
    public function setTasks(array $tasks)
    {
        $this->tasks = $tasks;

        return $this;
    }

    /**
     * @param AbstractTask $task
     *
     * @return $this
     */
    public function addTask(AbstractTask $task)
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
     * @param array|AbstractTask[] $tasks
     */
    public function revert(array $tasks)
    {
        // revert in reverse order of execution
        $tasks = array_reverse($tasks);

        /** @var AbstractTask[] $tasks */
        foreach ($tasks as $task) {
            // only revert executed tasks
            if ($task->isExecuted()) {
                $task->revert();
            }
        }
    }
}
