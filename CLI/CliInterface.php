<?php

namespace DigipolisGent\Domainator9k\CoreBundle\CLI;

interface CliInterface
{
    /**
     * Executes a command.
     *
     * @param string $command
     *   The (properly shell-escaped) command to execute.
     *
     * @return bool
     *   True on success, false on failure.
     */
    public function execute(string $command);

    /**
     * Get the output of the last execution.
     *
     * @return string
     */
    public function getLastOutput();
}
