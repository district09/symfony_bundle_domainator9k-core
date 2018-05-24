<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Event;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractEvent extends Event
{

    /**
     * The task object.
     *
     * @var Task
     */
    protected $task;

    /**
     * Class constructor.
     *
     * @param Task $task
     *   The task object.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the task object.
     *
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }
}
