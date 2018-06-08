<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Provisioner\ProvisionerInterface;

class ProvisionService
{

    /**
     * The build provisioners to run.
     *
     * @var ProvisionerInterface[]
     */
    protected $buildProvisioners;

    /**
     * The destroy provisioners to run.
     *
     * @var ProvisionerInterface[]
     */
    protected $destroyProvisioners;

    /**
     * Creates a new ProvisionService.
     *
     * @param ProvisionerInterface[] $buildProvisioners
     *   The provisioners to run on build tasks.
     * @param ProvisionerInterface[] $destroyProvisioners
     *   The provisioners to run on destroy tasks.
     */
    public function __construct(iterable $buildProvisioners, iterable $destroyProvisioners)
    {
        $this->buildProvisioners = $buildProvisioners;
        $this->destroyProvisioners = $destroyProvisioners;
    }

    /**
     * Run all provisioners for a task.
     *
     * @param Task $task
     *   The task to run the provisioners for.
     *
     * @return boolean
     *   True when the task has been processed succesfully, false for any other
     *   status.
     *
     * @throws \InvalidArgumentException
     *   If the task type is not supported.
     */
    public function run(Task $task)
    {
        switch ($task->getType()) {
            case Task::TYPE_BUILD:
                $this->build($task);
                break;

            case Task::TYPE_DESTROY:
                $this->destroy($task);
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Task type %s is not supported.', $task->getType()));
        }

        return $task->isProcessed();
    }

    /**
     * Run a build task.
     *
     * @param Task $task
     *   The build task.
     */
    protected function build(Task $task)
    {
        foreach ($this->buildProvisioners as $provisioner) {
            $provisioner->run($task);
            if ($task->isFailed()) {
                break;
            }
        }
    }

    /**
     * Run a destroy task.
     *
     * @param Task $task
     *   The destroy task.
     */
    protected function destroy(Task $task)
    {
        foreach ($this->destroyProvisioners as $provisioner) {
            $provisioner->run($task);
            if ($task->isFailed()) {
                break;
            }
        }
    }
}
