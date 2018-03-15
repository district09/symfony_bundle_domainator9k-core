<?php


namespace DigipolisGent\Domainator9k\CoreBundle\EventListener;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Event\AbstractEvent;
use DigipolisGent\Domainator9k\CoreBundle\Event\BuildEvent;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskLoggerService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class BuildEventListener
 * @package DigipolisGent\Domainator9k\CoreBundle\EventListener
 */
class BuildEventListener
{

    private $taskLoggerService;
    private $entityManager;

    /**
     * BuildEventListener constructor.
     * @param TaskLoggerService $taskLoggerService
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(TaskLoggerService $taskLoggerService, EntityManagerInterface $entityManager)
    {
        $this->taskLoggerService = $taskLoggerService;
        $this->entityManager = $entityManager;
    }

    public function onStart(BuildEvent $event)
    {
        $task = $event->getTask();
        $task->setStatus(Task::STATUS_IN_PROGRESS);
        $this->entityManager->persist($task);
        $this->entityManager->flush();
        $this->taskLoggerService->setTask($event->getTask());
    }

    public function onEnd(BuildEvent $event)
    {
        if ($event->getTask()->getStatus() == Task::STATUS_FAILED) {
            $this->taskLoggerService->addLine('Build failed');
            return;
        }

        $this->taskLoggerService->addLine('Build completed');
        $task = $event->getTask();
        $task->setStatus(Task::STATUS_PROCESSED);
        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }
}
