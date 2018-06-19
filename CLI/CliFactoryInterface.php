<?php

namespace DigipolisGent\Domainator9k\CoreBundle\CLI;

interface CliFactoryInterface
{
    public function create($object): ?CliInterface;
}
