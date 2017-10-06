<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Ssh;

interface ShellInterface
{
    /**
     * @param string    $command
     * @param string    &$stdout     will be filled with the command output STDOUT
     * @param bool|null &$exitStatus will be filled with the command exit status or null of unknown
     * @param string    &$stderr     will be filled with the command error output STDERR
     *
     * @return bool true if exit code === 0
     */
    public function exec($command, &$stdout = null, &$exitStatus = null, &$stderr = null);
}
