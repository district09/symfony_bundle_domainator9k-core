<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Command;

use DigipolisGent\Domainator9k\CoreBundle\Service\TaskRunnerService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class AbstractCommand extends ContainerAwareCommand
{

    /**
     * Run the next task of the specified type.
     *
     * @param string $type
     *   The task type.
     */
    protected function runNextTask(string $type)
    {
        $this->getContainer()
            ->get(TaskRunnerService::class)
            ->runNext($type);
    }
}
