<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironmentSettings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;

/**
 * Description of AppEnvironmentSettingsTest.
 *
 * @author Jelle Sebreghts
 */
class AppEnvironmentSettingsTest extends EntityTest
{
    public function getterTestDataProvider()
    {
        return [
            ['id', uniqid()],
            ['databaseHost', $this->getAlphaNumeric()],
            ['redisPassword', $this->getAlphaNumeric()],
            ['settings', $this->getMockBuilder(Settings::class)->getMock()],
            ['environment', $this->getAlphaNumeric()],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['databaseHost', $this->getAlphaNumeric()],
            ['redisPassword', $this->getAlphaNumeric()],
            ['settings', $this->getMockBuilder(Settings::class)->getMock()],
            ['environment', $this->getAlphaNumeric()],
        ];
    }

    protected function getEntity()
    {
        return new AppEnvironmentSettings();
    }
}
