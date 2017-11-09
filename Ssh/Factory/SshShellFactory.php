<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth\KeyFile;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth\Password;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShell;

/**
 * Description of ShellFactory
 *
 * @author Jelle Sebreghts
 */
class SshShellFactory implements SshShellFactoryInterface
{

    const AUTH_TYPE_KEY = 'key';
    const AUTH_TYPE_CREDENTIALS = 'credentials';

    /**
     * @var SshFactoryInterface
     */
    protected $sshFactory;

    /**
     * @param SshFactoryInterface $sshFactory
     */
    public function __construct(SshFactoryInterface $sshFactory)
    {
        $this->sshFactory = $sshFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create($host, $authType, $user, $password = null)
    {
        $auth = null;

        switch ($authType) {
            case static::AUTH_TYPE_KEY:
                $auth = new KeyFile($user, $password);
                break;
            case static::AUTH_TYPE_CREDENTIALS:
            default:
                $auth = new Password($user, $password);
                break;
        }

        return new SshShell($host, $auth, $this->sshFactory);
    }
}
