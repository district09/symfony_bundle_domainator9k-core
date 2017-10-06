<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth;

use phpseclib\Net\SSH2;

abstract class AbstractAuth
{
    abstract public function authenticate(SSH2 $connection);
}
