<?php

namespace DigipolisGent\Domainator9k\CoreBundle\CLI;

interface CliInterface
{
    /**
     * Executes a command.
     *
     * @param string $command
     *   The (properly shell-escaped) command to execute.
     */
    public function execute(string $command);
}
