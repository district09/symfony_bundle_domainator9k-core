<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Provider;

use DigipolisGent\Domainator9k\CoreBundle\CLI\CliFactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\CLI\CliInterface;
use DigipolisGent\Domainator9k\CoreBundle\Exception\NoCliFactoryFoundException;

class CliFactoryProvider {

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
     */
    public function createCliFor($object)
    {
        $class = get_class($object);

        if (!$class || !isset($this->cliFactories[$class])) {
            if (!($this->defaultCliFactory instanceof CliFactoryInterface)) {
                throw new NoCliFactoryFoundException('No cli factory found for ' . $class);
            }
            return $this->defaultCliFactory->create($object);
        }

        return $this->cliFactories[$class]->create($object);
    }
}
