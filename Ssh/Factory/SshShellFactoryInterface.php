<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShellInterface;

/**
 * Description of SshShellFactoryInterface.
 *
 * @author Jelle Sebreghts
 */
interface SshShellFactoryInterface
{
    /**
     * @param string $host
     *     The host to connect to.
     * @param string $authType
     *     The authentication type.
     * @param string $user
     *     The user to connect as.
     * @param string $password
     *     The password or key (depending on the authentication type).
     *
     * @return SshShellInterface
     */
    public function create($host, $authType, $user, $password = null);
}
