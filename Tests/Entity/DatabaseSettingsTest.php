<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\DatabaseSettings;

/**
 * Description of DatabaseSettingsTest.
 *
 * @author Jelle Sebreghts
 */
class DatabaseSettingsTest extends EntityTest
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Environment
     */
    protected $env;

    /**
     * @var string
     */
    protected $name;

    protected function setUp()
    {
        parent::setUp();
        $this->name = $this->getAlphaNumeric();
        $this->env = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $this->app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $this->app->expects($this->any())->method('getNameCanonical')->willReturn($this->getAlphaNumeric(false, 20));
        $this->env->expects($this->any())->method('getApplication')->willReturn($this->app);
    }

    public function testConstructor()
    {
        $settings = $this->getEntity();
        $this->assertEquals($this->env, $settings->getAppEnvironment());
        $this->assertEquals($this->name, $settings->getName());
        $this->assertNotRegExp('/[^a-zA-Z0-9_]+/', $settings->getUser());
        $this->assertLessThanOrEqual(16, strlen($settings->getUser()));
        $this->assertNotRegExp('/[^a-zA-Z0-9]+/', $settings->getPassword());
        $this->assertEquals(10, strlen($settings->getPassword()));
    }

    public function testGetEngine()
    {
        $settings = $this->getEntity();
        $settings->setEngine(null);
        $this->assertEquals('mysql', $settings->getEngine());
    }

    public function getterTestDataProvider()
    {
        return [
            ['id', uniqid()],
            ['appEnvironment', $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock()],
            ['name', $this->getAlphaNumeric()],
            ['host', $this->getAlphaNumeric()],
            ['port', $this->getAlphaNumeric()],
            ['user', $this->getAlphaNumeric()],
            ['password', $this->getAlphaNumeric()],
            ['sockDatabaseId', uniqid()],
            ['isCreated', (bool) mt_rand(0, 1), true, ''],
            ['engine', $this->getAlphaNumeric()],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['appEnvironment', $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock()],
            ['name', $this->getAlphaNumeric()],
            ['host', $this->getAlphaNumeric()],
            ['port', $this->getAlphaNumeric()],
            ['user', $this->getAlphaNumeric()],
            ['password', $this->getAlphaNumeric()],
            ['sockDatabaseId', uniqid()],
            ['isCreated', (bool) mt_rand(0, 1), true, ''],
            ['engine', $this->getAlphaNumeric()],
        ];
    }

    /**
     * @return DatabaseSettings
     */
    protected function getEntity()
    {
        return new DatabaseSettings($this->env, $this->name);
    }
}
