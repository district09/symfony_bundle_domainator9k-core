<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory;

use phpseclib\Net\SFTP;
use phpseclib\Net\SSH2;

/**
 * Description of SshFactory
 *
 * @author Jelle Sebreghts
 */
class SshFactory implements SshFactoryInterface
{

    public function getSftpConnection($host, $port = 22, $timeout = 10)
    {
        return new SFTP($host, $port, $timeout);
    }

    /**
     * {@inheritdoc}
     */
    public function getSshConnection($host, $port = 22, $timeout = 10)
    {
        $connection = new SSH2($host, $port, $timeout);
        $connection->_connect();
        return $connection;
    }

}
