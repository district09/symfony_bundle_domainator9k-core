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
     */
    public function execute(string $command)
    {
        $this->connection->exec($command);
    }
}
