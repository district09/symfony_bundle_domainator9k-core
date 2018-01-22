<?php


namespace DigipolisGent\Domainator9k\CoreBundle\EventListener;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Event\BuildEvent;
use DigipolisGent\Domainator9k\CoreBundle\Event\DestroyEvent;
use DigipolisGent\Domainator9k\CoreBundle\Service\BuildLoggerService;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskLoggerService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class DestroyEventListener
 * @package DigipolisGent\Domainator9k\CoreBundle\EventListener
 */
class DestroyEventListener
{

    private $taskLoggerService;
    private $entityManager;

    /**
     * BuildEventListener constructor.
     * @param BuildLoggerService $buildLoggerService
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(TaskLoggerService $taskLoggerService, EntityManagerInterface $entityManager)
    {
        $this->taskLoggerService = $taskLoggerService;
        $this->entityManager = $entityManager;
    }

    /**
     * @param BuildEvent $event
     */
    public function onStart(DestroyEvent $event)
    {
        $task = $event->getTask();
        $task->setStatus(Task::STATUS_IN_PROGRESS);
        $this->entityManager->persist($task);
        $this->entityManager->flush();
        $this->taskLoggerService->setTask($event->getTask());
    }

    /**
     * @param BuildEvent $event
     */
    public function onEnd(DestroyEvent $event)
    {
        $task = $event->getTask();
        $task->setStatus(Task::STATUS_PROCESSED);
        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }
}
