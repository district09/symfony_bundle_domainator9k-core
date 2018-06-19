<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Provider;

use DigipolisGent\Domainator9k\CoreBundle\CLI\CliFactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\CLI\CliInterface;
use DigipolisGent\Domainator9k\CoreBundle\Exception\NoCliFactoryFoundException;

class CliFactoryProvider
{

    /**
     * @var CliFactoryInterface[]
     */
    protected $cliFactories;

    /**
     * @var CliFactoryInterface
     */
    protected $defaultCliFactory;


    public function __construct(CliFactoryInterface $defaultCliFactory = null)
    {
        $this->defaultCliFactory = $defaultCliFactory;
    }

    public function registerCliFactory(CliFactoryInterface $cliFactory, $class)
    {
        $this->cliFactories[$class] = $cliFactory;
    }

    /**
     * @param mixed $object
     *
     * @return CliInterface
     *
     * @throws \InvalidArgumentException
     * @throws NoCliFactoryFoundException
     */
    public function createCliFor($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s::createCliFor() expects parameter 1 to be an object, %s given.',
                    get_called_class(),
                    gettype($object)
                )
            );
        }

        $class = get_class($object);

        if (!isset($this->cliFactories[$class])) {
            if (!($this->defaultCliFactory instanceof CliFactoryInterface)) {
                throw new NoCliFactoryFoundException(
                    sprintf('No cli factory found for %s and no default factory given.', $class)
                );
            }
            return $this->defaultCliFactory->create($object);
        }

        return $this->cliFactories[$class]->create($object);
    }
}
