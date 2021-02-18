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
        $this->getContainer
            // @deprecated
            // This is deprecated in Symfony 4, but we leave it here
            // intentionally so we'll _have_ to fix it once we upgrade from 3 to
            // 4. The only way to do it in 3.4 is to make the service public,
            // which we don't want. Symfony 4 supports injecting service
            // dependencies in commands in the constructor (allowing them to be
            // private, Symfony 3 doesn't.
            ->get(TaskRunnerService::class)
            ->runNext($type);
    }
}
