<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Provider;

use DigipolisGent\Domainator9k\CoreBundle\CacheClearer\CacheClearerInterface;
use DigipolisGent\Domainator9k\CoreBundle\Exception\NoCacheClearerFoundException;
use Doctrine\ORM\Proxy\Proxy;

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
     * @throws \InvalidArgumentException
     * @throws NoCacheClearerFoundException
     */
    public function getCacheClearerFor($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s::getCacheClearerFor() expects parameter 1 to be an object, %s given.',
                    get_called_class(),
                    gettype($object)
                )
            );
        }

        $class = get_class($object);
        if (!isset($this->cacheClearers[$class]) && $object instanceof Proxy) {
            $class = get_parent_class($object);
        }
        if (isset($this->cacheClearers[$class])) {
            return $this->cacheClearers[$class];
        }

        throw new NoCacheClearerFoundException('No cache clearer found for ' . $class);
    }
}
