<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

interface TaskRunnerInterface
{
    /**
     * @return array
     */
    public function getTasks();

    /**
     * @param array $tasks
     *
     * @return $this
     */
    public function setTasks(array $tasks);

    /**
     * @param TaskInterface $task
     *
     * @return $this
     */
    public function addTask(TaskInterface $task);

    /**
     * @return $this
     */
    public function clearTasks();

    /**
     * @return TaskResult
     */
    public function run();

    /**
     * @param array|TaskInterface[] $tasks
     */
    public function revert(array $tasks);
}
