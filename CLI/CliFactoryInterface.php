<?php

namespace DigipolisGent\Domainator9k\CoreBundle\CLI;

interface CliFactoryInterface
{
    /**
     * Creates a CLI instance for a given object.
     *
     * @param mixed $object
     *   The object to get the CLI instance for.
     *
     * @return CliInterface
     *   The CLI for the given object.
     */
    public function create($object): ?CliInterface;
}
