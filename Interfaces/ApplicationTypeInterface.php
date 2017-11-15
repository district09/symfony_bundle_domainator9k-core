<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Interfaces;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;

/**
 * Generic interface for application types.
 */
interface ApplicationTypeInterface
{
    /**
     * Get the config files for this application type.
     *
     * @param AppEnvironment $env
     * @param array $servers
     * @param Settings $settings
     *
     * @return string[]
     *     An array keyed by path with the file contents as value.
     */
    public function getConfigFiles(AppEnvironment $env, array $servers, Settings $settings);

    public function getPublicFolder();

    public function getSlug();

    public function getDirectories($user);
}
