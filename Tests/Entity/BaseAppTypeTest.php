<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Service\AppTypeSettingsService;
use DigipolisGent\Domainator9k\CoreBundle\Service\EnvironmentService;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Stub\AppType\TestAppType;
use ReflectionObject;

/**
 * Description of AppEnvironmentSettingsTest.
 *
 * @author Jelle Sebreghts
 */
class BaseAppTypeTest extends EntityTest
{
    public function testParseYamlConfig()
    {
        $type = $this->getEntity();
        $type->parseYamlConfig();
        $this->assertEquals('config123', $type->getSiteConfig());
        $this->assertEquals('Stub', $type->getName());
        $this->assertEquals('stub', $type->getSlug());
        $this->assertEquals('cron123', $type->getCron());
        $user = $this->getAlphaNumeric();
        $this->assertEquals(['/home/' . $user . '/stubfolder'], $type->getDirectories($user));
        $this->assertEquals('publictestfolder', $type->getPublicFolder());
        $this->assertTrue($type->isDatabaseRequired());
        $this->assertEquals('config123', $type->getSiteConfig());

        $refObject = new ReflectionObject($type);
        $refProperty = $refObject->getProperty('ymlConfigName');
        $refProperty->setAccessible(true);
        $refProperty->setValue($type, 'custom_stub_config.yml');

        $type->parseYamlConfig();
        $this->assertEquals('custom config123', $type->getSiteConfig());
        $this->assertEquals('Custom Stub', $type->getName());
        $this->assertEquals('custom_stub', $type->getSlug());
        $this->assertEquals('custom cron123', $type->getCron());
        $user = $this->getAlphaNumeric();
        $this->assertEquals(['/home/' . $user . '/customstubfolder'], $type->getDirectories($user));
        $this->assertEquals('custompublictestfolder', $type->getPublicFolder());
        $this->assertFalse($type->isDatabaseRequired());
        $this->assertEquals('custom config123', $type->getSiteConfig());
    }

    public function testGetDirectories()
    {
        $type = $this->getEntity();
        $user = $this->getAlphaNumeric();
        $refObject = new ReflectionObject($type);
        $refProperty = $refObject->getProperty('directories');
        $refProperty->setAccessible(true);
        $refProperty->setValue($type, ['/home/[[user]]/stubfolder']);
        $this->assertEquals(['/home/' . $user . '/stubfolder'], $type->getDirectories($user));
    }

    public function getterTestDataProvider()
    {
        return [
            ['environmentService', $this->getMockBuilder(EnvironmentService::class)->disableOriginalConstructor()->getMock()],
            ['appTypeSettingsService', $this->getMockBuilder(AppTypeSettingsService::class)->disableOriginalConstructor()->getMock()],
            ['name', $this->getAlphaNumeric()],
            ['slug', $this->getAlphaNumeric()],
            ['cron', $this->getAlphaNumeric()],
            ['publicFolder', $this->getAlphaNumeric()],
            ['databaseRequired', (bool) mt_rand(0, 1), true],
            ['siteConfig', $this->getAlphaNumeric()],
            ['settingsFormClass', $this->getAlphaNumeric()],
            ['settingsEntityClass', $this->getAlphaNumeric()],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['environmentService', $this->getMockBuilder(EnvironmentService::class)->disableOriginalConstructor()->getMock()],
            ['appTypeSettingsService', $this->getMockBuilder(AppTypeSettingsService::class)->disableOriginalConstructor()->getMock()],
        ];
    }

    /**
     * @return TestAppType
     */
    protected function getEntity()
    {
        return new TestAppType();
    }
}
