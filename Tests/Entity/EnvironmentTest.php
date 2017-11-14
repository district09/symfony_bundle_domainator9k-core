<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;

/**
 * Description of AppEnvironmentSettingsTest.
 *
 * @author Jelle Sebreghts
 */
class EnvironmentTest extends EntityTest
{
    public function testConstructor()
    {
        $env = $this->getEntity();
        $this->assertFalse($env->isProd());
        $this->assertEmpty($env->getUrlStructure());
    }

    public function testToString()
    {
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
     * @return Environment
     */
    protected function getEntity()
    {
        return new Environment();
    }
}
