<?php

namespace DigipolisGent\Domainator9k\CoreBundle\CacheClearer;

use DigipolisGent\Domainator9k\CoreBundle\CLI\CliInterface;

interface CacheClearerInterface
{
    /**
     * @param mixed $object
     *   The object to clear the cache for.
     * @param CliInterface $cli
     *   The cli to execute the cache clear command on.
     *
     * @return bool
     *   True on success, false on failure.
     */
    public function clearCache($object, CliInterface $cli);
}
