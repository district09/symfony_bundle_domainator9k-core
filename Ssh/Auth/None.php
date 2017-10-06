<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth;

use phpseclib\Net\SSH2;

class None extends AbstractAuth
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function authenticate(SSH2 $connection)
    {
        if (!$connection->login($this->user)) {
            throw new \RuntimeException(sprintf(
                "fail: unable to authenticate user '%s', using password: NO",
                $this->user
            ));
        }
    }
}
