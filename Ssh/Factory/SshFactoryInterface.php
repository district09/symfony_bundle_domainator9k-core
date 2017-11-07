<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory;

use phpseclib\Net\SSH2;

/**
 *
 * @author Jelle Sebreghts
 */
interface SshFactoryInterface
{
    /**
     *
     * @param string $host
     *     The host to connect to.
     * @param string $port
     *     The port to connect to.
     * @param int $timeout
     *     The timeout for this connection.
     *
     * @return SSH2
     */
    public function getSshConnection($host, $port = 22, $timeout = 10);

    public function getSftpConnection($host, $port = 22, $timeout = 10);
}
