<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\BaseCiAppTypeSettings;

/**
 * Description of AppEnvironmentSettingsTest.
 *
 * @author Jelle Sebreghts
 */
class BaseCiAppTypeSettingsTest extends EntityTest
{
    /**
     * @var string
     */
    protected $ciTypeSlug;

    /**
     * @var string
     */
    protected $appTypeSlug;

    protected function setUp()
    {
        parent::setUp();
        $this->ciTypeSlug = $this->getAlphaNumeric();
        $this->appTypeSlug = $this->getAlphaNumeric();
    }

    public function testConstructor()
    {
        $settings = $this->getEntity();
        $this->assertEquals($this->appTypeSlug, $settings->getAppTypeSlug());
        $this->assertEquals($this->ciTypeSlug, $settings->getCiTypeSlug());
    }

    public function getterTestDataProvider()
    {
        return [
            ['appTypeSlug', $this->getAlphaNumeric()],
            ['ciTypeSlug', $this->getAlphaNumeric()],
            ['enabled', (bool) mt_rand(0, 1), true],
            ['additionalConfig', $this->getAlphaNumeric()],
            ['appId', uniqid()],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['appTypeSlug', $this->getAlphaNumeric()],
            ['ciTypeSlug', $this->getAlphaNumeric()],
            ['enabled', (bool) mt_rand(0, 1), true],
            ['additionalConfig', $this->getAlphaNumeric()],
            ['appId', uniqid()],
        ];
    }

    /**
     * @return BaseCiAppTypeSettings
     */
    protected function getEntity()
    {
        return $this->getMockBuilder(BaseCiAppTypeSettings::class)->setConstructorArgs([$this->ciTypeSlug, $this->appTypeSlug])->getMockForAbstractClass();
    }
}
