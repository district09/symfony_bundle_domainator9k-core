<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironmentSettings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\EntityTest;

/**
 * Description of AppEnvironmentSettingsTest
 *
 * @author Jelle Sebreghts
 */
class AppEnvironmentSettingsTest extends EntityTest
{
    public function getterTestDataProvider()
    {
        return [
            ['id', uniqid()],
            ['databaseHost', uniqid()],
            ['redisPassword', uniqid()],
            ['settings', $this->getMockBuilder(Settings::class)->getMock()],
            ['environment', uniqid()],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['databaseHost', uniqid()],
            ['redisPassword', uniqid()],
            ['settings', $this->getMockBuilder(Settings::class)->getMock()],
            ['environment', uniqid()],
        ];
    }

    protected function getEntity()
    {
        return new AppEnvironmentSettings();
    }

}
