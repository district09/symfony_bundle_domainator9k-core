<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\BaseCiAppTypeSettings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\EntityTest;

/**
 * Description of AppEnvironmentSettingsTest
 *
 * @author Jelle Sebreghts
 */
class EnvironmentTest extends EntityTest
{

    /**
     *
     * @var string
     */
    protected $ciTypeSlug;

    /**
     *
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
        $env = $this->getEntity();
        $this->assertFalse($env->isProd());
        $this->assertEmpty($env->getUrlStructure());
    }

    public function testToString() {
        $env = $this->getEntity();
        $name = $this->getAlphaNumeric();
        $env->setName($name);
        $this->assertEquals($name, (string) $env);
    }

    public function getterTestDataProvider()
    {
        return [
            ['devPermissions', (bool) mt_rand(0, 1), true],
            ['id', uniqid()],
            ['name', $this->getAlphaNumeric()],
            ['prod', (bool) mt_rand(0, 1), true],
            ['urlStructure', $this->getAlphaNumeric()],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['devPermissions', (bool) mt_rand(0, 1), true],
            ['name', $this->getAlphaNumeric()],
            ['prod', (bool) mt_rand(0, 1), true],
            ['urlStructure', $this->getAlphaNumeric()],
        ];
    }

    /**
     *
     * @return BaseCiAppTypeSettings
     */
    protected function getEntity()
    {
        return new Environment();
    }

}
