<?php

namespace DigipolisGent\Domainator9k\CoreBundle\EntityService;

use Ctrl\Common\EntityService\AbstractDoctrineService;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;

class SettingsService extends AbstractDoctrineService
{
    /**
     * @return string
     */
    public function getEntityClass()
    {
        return Settings::class;
    }

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return $this->getFinder()->get(1);
    }

    /**
     * Apply all defaults to a new Application.
     *
     * @param Application $app
     */
    public function applyDefaults(Application $app)
    {
        $settings = $this->getSettings();

        foreach ($app->getAppEnvironments() as $env) {
            if ((null !== $env->getDatabaseSettings())) {
                $env->getDatabaseSettings()->setHost(
                    $settings->getAppEnvironmentSettings($env)->getDatabaseHost()
                );
            }
        }
    }
}
