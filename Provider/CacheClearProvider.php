<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Provider;

use DigipolisGent\Domainator9k\CoreBundle\CacheClearer\CacheClearerInterface;
use DigipolisGent\Domainator9k\CoreBundle\Exception\NoCacheClearerFoundException;

class CacheClearProvider
{
    protected $cacheClearers;

    public function registerCacheClearer(CacheClearerInterface $clearer, $class)
    {
        $this->cacheClearers[$class] = $clearer;
    }

    /**
     * @param mixed $object
     *
     * @return CacheClearerInterface
     *
     * @throws NoCacheClearerFoundException
     */
    public function getCacheClearerFor($object)
    {
        $class = get_class($object);

        if (!$class || !isset($this->cacheClearers[$class])) {
            throw new NoCacheClearerFoundException('No cache clearer found for ' . $class);
        }

        return $this->cacheClearers[$class];
    }
}
