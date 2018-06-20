<?php

namespace DigipolisGent\Domainator9k\CoreBundle\CLI;

use phpseclib\Net\SSH2;

class RemoteCli implements CliInterface
{

    /**
     * @var SSH2
     */
    protected $connection;

    /**
     * @var string
     */
    protected $lastOutput;


    /**
     * RemoteCli class constructor.
     *
     * @param SSH2 $connection
     *   The ssh connection to execute the commands on.
     *
     * @param string $cwd
     *   The current working directory to execute the commands from.
     */
    public function __construct(SSH2 $connection, $cwd = null)
    {
        $this->connection = $connection;
        if ($cwd) {
            $this->execute('cd -P ' . escapeshellarg($cwd));
        }
    }

    /**
     * Executes a command.
     *
     * @param string $command
     *   The (properly shell-escaped) command to execute.
     *
     * @return bool
     *   True on success, false on failure.
     */
    public function execute(string $command)
    {
        $result = $this->connection->exec($command);
        $this->lastOutput = $result ? $result : '';

        return $this->connection->getExitStatus() === 0;
    }

    /**
     * Get the output of the last execution.
     *
     * @return string
     */
    public function getLastOutput()
    {
        return $this->lastOutput;
    }

}
