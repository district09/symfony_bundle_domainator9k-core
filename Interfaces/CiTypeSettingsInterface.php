<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Interfaces;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;

/**
 * @author Jelle Sebreghts
 */
interface CiTypeSettingsInterface
{
    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param AppEnvironment $env
     *
     * @return string
     */
    public function getJobName(AppEnvironment $env);
}
