<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Provider;

interface TemplateProviderInterface
{
    public function listTemplates($class);

    public function registerReplacements($type, $object);
}
