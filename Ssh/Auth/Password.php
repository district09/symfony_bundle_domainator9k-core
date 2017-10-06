<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth;

use phpseclib\Net\SSH2;

class Password extends AbstractAuth
{
    protected $user;

    protected $password;

    public function __construct($user, $password = null)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function authenticate(SSH2 $connection)
    {
        if ($this->password === null) {
            $authenticator = new None($this->user);
            $authenticator->authenticate($connection);

            return;
        }
        if (!$connection->login($this->user, $this->password)) {
            throw new \RuntimeException(sprintf(
                "fail: unable to authenticate user '%s', using password: YES",
                $this->user
            ));
        }
    }
}
