<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Event;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use Symfony\Component\EventDispatcher\Event;

class AbstractEvent extends Event
{
    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function getTask()
    {
        return $this->task;
    }
}
