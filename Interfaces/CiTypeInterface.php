<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Interfaces;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;

interface CiTypeInterface
{
    public function getName();

    public function getSlug();

    public function getYmlConfigName();

    public function getAdditionalConfig();

    public function getSettingsFormClass();

    public function getSettingsEntityClass();

    public function getAppTypeSettingsFormClass();

    public function getAppTypeSettingsEntityClass();

    public function getProcessorServiceClass();

    public function getMenuUrlFieldName();

    public function buildCiUrl($settings, AppEnvironment $env);

    public function buildUrl($ciTypeSettings);
}
