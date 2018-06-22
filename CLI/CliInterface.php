<?php

namespace DigipolisGent\Domainator9k\CoreBundle\CLI;

use DigipolisGent\CommandBuilder\CommandBuilder;

interface CliInterface
{
    /**
     * Executes a command.
     *
     * @param CommandBuilder $command
     *   The command to execute.
     *
     * @return bool
     *   True on success, false on failure.
     */
    public function execute(CommandBuilder $command);

    /**
     * Get the output of the last execution.
     *
     * @return string
     */
    public function getLastOutput();
}
