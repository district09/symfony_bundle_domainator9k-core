<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TaskLoggerService
 * @package DigipolisGent\Domainator9k\CoreBundle\Service
 */
class TaskLoggerService
{

    private $entityManager;
    private $task;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Build $build
     */
    public function setTask(Task $task)
    {
        $this->task = $task;
    }

    /**
     * @param string $line
     */
    public function addLine(string $line)
    {
        $log = $this->task->getLog();
        $log .= $line . PHP_EOL;

        $this->task->setLog($log);
        $this->entityManager->persist($this->task);
        $this->entityManager->flush();
    }
}
