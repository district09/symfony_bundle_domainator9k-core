<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ServerSettings;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\EntityTest;

/**
 * Description of ServerSettingsTest
 *
 * @author Jelle Sebreghts
 */
class ServerSettingsTest extends EntityTest
{

    /**
     *
     * @var AppEnvironment
     */
    protected $env;

    /**
     *
     * @var string
     */
    protected $user;

    /**
     *
     * @var string
     */
    protected $password;

    protected function setUp()
    {
        parent::setUp();
        $this->env = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $this->user = $this->getAlphaNumeric();
        $this->password = $this->getAlphaNumeric();
    }

    public function testConstructor()
    {
        $settings = $this->getEntity();
        $this->assertEquals($this->env, $settings->getAppEnvironment());
        $this->assertEquals($this->user, $settings->getUser());
        $this->assertEquals($this->password, $settings->getPassword());
    }

    public function getterTestDataProvider()
    {
        return [
            ['id', uniqid()],
            ['appEnvironment', $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock()],
            ['portSsh', uniqid()],
            ['user', $this->getAlphaNumeric()],
            ['password', $this->getAlphaNumeric()],
            ['sockAccountId', uniqid()],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['appEnvironment', $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock()],
            ['portSsh', uniqid()],
            ['user', $this->getAlphaNumeric()],
            ['password', $this->getAlphaNumeric()],
            ['sockAccountId', uniqid()],
        ];
    }

    /**
     *
     * @return ServerSeettings
     */
    protected function getEntity()
    {
        return new ServerSettings($this->env, $this->user, $this->password);
    }

}
