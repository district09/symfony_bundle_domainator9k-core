<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Interfaces;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;

interface ApplicationTypeInterface
{
    public function getConfigFiles(AppEnvironment $env, array $servers, Settings $settings);

    public function getPublicFolder();
}
