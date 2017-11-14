<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth;

use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;

class KeyFile extends AbstractAuth
{
    protected $user;

    protected $publicKeyFile;

    protected $privateKeyFile;

    protected $passphrase;

    public function __construct($user, $privateKeyFile, $passphrase = null)
    {
        $this->user = $user;
        $this->privateKeyFile = $privateKeyFile;
        $this->publicKeyFile = $privateKeyFile . '.pub';
        $this->passphrase = $passphrase;
    }

    public function authenticate(SSH2 $connection)
    {
        $rsa = new RSA();
        $rsa->loadKey(file_get_contents($this->privateKeyFile));
        $rsa->setPublicKey(file_get_contents($this->publicKeyFile));
        if (!is_null($this->passphrase)) {
            $rsa->setPassword($this->passphrase);
        }
        if (!$connection->login($this->user, $rsa)) {
            throw new \RuntimeException(sprintf(
                "fail: unable to authenticate user '%s' using key file",
                $this->user
            ));
        }
    }
}
