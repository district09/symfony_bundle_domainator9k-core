<?php

namespace DigipolisGent\Domainator9k\CoreBundle\CLI;

use phpseclib\Net\SSH2;

class RemoteCli implements CliInterface
{

    /**
     * @var SSH2
     */
    protected $connection;


    public function __construct(SSH2 $connection, $cwd = null)
    {
        $this->connection = $connection;
        if ($cwd) {
            $this->connection->exec('cd -P ' . escapeshellarg($cwd));
        }
    }

    public function execute(string $command)
    {
        $this->connection->exec($command);
    }
}
