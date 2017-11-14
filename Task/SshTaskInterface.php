<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactoryInterface;

/**
 * Description of SshTaskInterface.
 *
 * @author Jelle Sebreghts
 */
interface SshTaskInterface extends TaskInterface
{
    /**
     * @param SshShellFactoryInterface $sshShellFactory
     *
     * @return $this
     */
    public function setSshShellFactory(SshShellFactoryInterface $sshShellFactory);
}
