<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Interfaces;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;

interface CiTypeInterface
{

    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the slug.
     *
     * @return string
     */
    public function getSlug();

    /**
     * Gets the YAML config name.
     *
     * @return string
     */
    public function getYmlConfigName();

    /**
     * Gets the additional config.
     *
     * @return string
     */
    public function getAdditionalConfig();

    /**
     * Gets the settings form class.
     *
     * @return string
     */
    public function getSettingsFormClass();

    /**
     * Gets the settings entity class.
     *
     * @return string
     */
    public function getSettingsEntityClass();

    /**
     * Gets the app type settings form class.
     *
     * @return string
     */
    public function getAppTypeSettingsFormClass();

    /**
     * Gets the app type settings entity class.
     *
     * @return string
     */
    public function getAppTypeSettingsEntityClass();

    /**
     * Gets the processor service class.
     *
     * @return string
     */
    public function getProcessorServiceClass();

    /**
     * Gets the menu field name.
     *
     * @return string
     */
    public function getMenuUrlFieldName();

    /**
     * Gets the url for the build job.
     *
     * @param \DigipolisGent\Domainator9k\CoreBundle\Interfaces\CiTypeSettingsInterface $settings
     * @param AppEnvironment $env
     *
     * @return string
     */
    public function buildCiUrl(CiTypeSettingsInterface $settings, AppEnvironment $env);

    /**
     * Gets the main url for the ci tool.
     *
     * @param \DigipolisGent\Domainator9k\CoreBundle\Interfaces\CiTypeSettingsInterface $ciTypeSettings
     *
     * @return string
     */
    public function buildUrl(CiTypeSettingsInterface $ciTypeSettings);
}
