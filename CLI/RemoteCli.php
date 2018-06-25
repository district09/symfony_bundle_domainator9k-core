<?php

namespace DigipolisGent\Domainator9k\CoreBundle\CLI;

use DigipolisGent\CommandBuilder\CommandBuilder;
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
     * @var string
     */
    protected $cwd;

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
        $this->cwd = $cwd;
    }

    /**
     * Executes a command.
     *
     * @param CommandBuilder $command
     *   The command to execute.
     *
     * @return bool
     *   True on success, false on failure.
     */
    public function execute(CommandBuilder $command)
    {
        if ($this->cwd) {
            $command = CommandBuilder::create('cd')
                ->addFlag('P')
                ->addArgument($this->cwd)
                ->onSuccess($command);
        }
        $this->lastOutput = 'Executing ' . $command . "\n";
        $result = $this->connection->exec($command->getCommand());
        $this->lastOutput .= $result ? $result : '';

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
