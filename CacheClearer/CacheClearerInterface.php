<?php

namespace DigipolisGent\Domainator9k\CoreBundle\CacheClearer;

use DigipolisGent\Domainator9k\CoreBundle\CLI\CliInterface;

interface CacheClearerInterface
{
    public function clearCache($object, CliInterface $cli);
}
