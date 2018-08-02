<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;

abstract class AbstractProvisioner implements ProvisionerInterface
{
    /**
     * The task to run this provisioner on.
     *
     * @var Task
     */
    protected $task;

    /**
     * @param Task $task
     */
    public final function setTask(Task $task)
    {
        $this->task = $task;
    }

    public final function run()
    {
        if (!($this->task instanceof Task)) {
            throw new \LogicException('A task must be set before running a provisioner.');
        }
        $this->doRun();
    }

    abstract protected function doRun();

    public function isExecutedByDefault()
    {
        return true;
    }
}
