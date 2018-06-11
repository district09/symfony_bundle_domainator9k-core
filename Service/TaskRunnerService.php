<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Exception\LoggedException;
use DigipolisGent\Domainator9k\CoreBundle\Provisioner\ProvisionerInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TaskRunnerService
 *
 * @package DigipolisGent\Domainator9k\CoreBundle\Service
 */
class TaskRunnerService
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
     * The entity manager service.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * The task logger.
     *
     * @var TaskLoggerService
     */
    protected $logger;

    /**
     * Creates a new TaskRunnerService.
     *
     * @param ProvisionerInterface[] $buildProvisioners
     *   The provisioners to run on build tasks.
     * @param ProvisionerInterface[] $destroyProvisioners
     *   The provisioners to run on destroy tasks.
     * @param EntityManagerInterface $entityManager
     *   The entity manager service.
     */
    public function __construct(
        iterable $buildProvisioners,
        iterable $destroyProvisioners,
        EntityManagerInterface $entityManager,
        TaskLoggerService $logger
    ) {
        $this->buildProvisioners = $buildProvisioners;
        $this->destroyProvisioners = $destroyProvisioners;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Run a task.
     *
     * @param Task $task
     *   The task to run.
     *
     * @return boolean
     *   True when the task has been processed succesfully, false for any other
     *   status.
     */
    public function run(Task $task)
    {
        if (!$task->isNew()) {
            throw new \InvalidArgumentException(sprintf('Task "%s" cannot be restarted.', $task->getId()));
        }

        // Set the task in progress.
        $task->setInProgress();
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->runProvisioners($task);

        // Update the status.
        if ($task->isInProgress()) {
            $task->setProcessed();
        }

        // Add a log message or simply persist any changes.
        switch (true) {
            case $task->isProcessed():
                $this->logger->addLogMessage($task, '', '', 0);
                $this->logger->addSuccessLogMessage($task, 'Task run completed.', 0);
                break;

            case $task->isFailed():
                $this->logger->addLogMessage($task, '', '', 0);
                $this->logger->addFailedLogMessage($task, 'Task run failed.', 0);
                break;

            default:
                $this->entityManager->persist($task);
                $this->entityManager->flush();
                break;
        }

        return $task->isProcessed();
    }

    /**
     * Run all provisioners for a task.
     *
     * @param Task $task
     *   The task to run the provisioners for.
     *
     * @throws \InvalidArgumentException
     *   If the task type is not supported.
     */
    protected function runProvisioners(Task $task)
    {
        $provisioners = [];
        switch ($task->getType()) {
            case Task::TYPE_BUILD:
                $provisioners = $this->buildProvisioners;
                break;

            case Task::TYPE_DESTROY:
                $provisioners = $this->destroyProvisioners;
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Task type %s is not supported.', $task->getType()));
        }
        try {
            foreach ($provisioners as $provisioner) {
                $provisioner->setTask($task);
                $provisioner->run();
                if ($task->isFailed()) {
                    break;
                }
            }
        } catch (\Exception $ex) {
            $task->setFailed();
            if (!($ex instanceof LoggedException)) {
                $this->logger
                    ->addErrorLogMessage($task, $ex->getMessage(), 2)
                    ->addFailedLogMessage($task, sprintf('Provisioner %s failed.', $provisioner->getName()));
            }
        }
    }

    /**
     * Run the next task of the specified type.
     *
     * @param string $type
     *   The task type to run.
     *
     * @return boolean
     *   True on success, false on failure.
     */
    public function runNext(string $type)
    {
        $task = $this->entityManager
            ->getRepository(Task::class)
            ->getNextTask($type);

        if ($task) {
            return $this->run($task);
        }

        return true;
    }

    /**
     * Cancel a task.
     *
     * @param Task $task
     *   The task to cancel.
     */
    public function cancel(Task $task)
    {
        if ($task->getStatus() !== Task::STATUS_NEW) {
            throw new \InvalidArgumentException(sprintf('Task %s cannot be cancelled.', $task->getId()));
        }

        $task->setStatus(Task::STATUS_CANCEL);
        $this->logger->addInfoLogMessage($task, 'Task run cancelled.');
    }
}
