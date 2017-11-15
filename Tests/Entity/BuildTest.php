<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use Ctrl\RadBundle\Entity\User;
use DateTime;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;

/**
 * Description of BuildTest.
 *
 * @author Jelle Sebreghts
 */
class BuildTest extends EntityTest
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var AppEnvironment
     */
    protected $env;

    protected function setUp()
    {
        parent::setUp();
        $this->app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $this->env = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $this->type = $this->getAlphaNumeric();
    }

    public function testConstructor()
    {
        $build = $this->getEntity();
        $this->assertEquals($this->type, $build->getType());
        $this->assertEquals($this->env, $build->getAppEnvironment());
        $this->assertEquals($this->app, $build->getApplication());
        $this->assertInstanceOf(DateTime::class, $build->getTimestamp());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage environment is required when creating a DEPLOY build
     */
    public function testDeployBuildNoAppEnv()
    {
        new Build($this->app, Build::TYPE_DEPLOY);
    }

    public function testProvisionBuildNoAppEnv()
    {
        $build = new Build($this->app, Build::TYPE_PROVISION);
        $this->assertEquals(Build::TYPE_PROVISION, $build->getType());
        $this->assertEmpty($build->getAppEnvironment());
        $this->assertEquals($this->app, $build->getApplication());
        $this->assertInstanceOf(DateTime::class, $build->getTimestamp());
    }

    public function testSetUser()
    {
        $build = $this->getEntity();
        $username = $this->getAlphaNumeric();
        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $user->expects($this->any())->method('getUsername')->willReturn($username);
        $build->setUser($user);
        $this->assertEquals($username, $build->getDeletedUser());
    }

    public function testSetCompleted()
    {
        $build = $this->getEntity();
        $build->setPid(uniqid());
        $build->setCompleted(true);
        $this->assertEmpty($build->getPid());
    }

    public function getterTestDataProvider()
    {
        return [
            ['id', uniqid()],
            ['type', $this->getAlphaNumeric()],
            ['user', $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock()],
            ['deletedUser', $this->getAlphaNumeric()],
            ['application', $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock()],
            ['appEnvironment', $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock()],
            ['timestamp', new DateTime()],
            ['log', $this->getAlphaNumeric()],
            ['started', (bool) mt_rand(0, 1), true],
            ['completed', (bool) mt_rand(0, 1), true],
            ['success', (bool) mt_rand(0, 1)],
            ['pid', uniqid()],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['type', $this->getAlphaNumeric()],
            ['user', $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock()],
            ['application', $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock()],
            ['appEnvironment', $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock()],
            ['timestamp', new DateTime()],
            ['log', $this->getAlphaNumeric()],
            ['started', (bool) mt_rand(0, 1), true],
            ['completed', (bool) mt_rand(0, 1), true],
            ['success', (bool) mt_rand(0, 1)],
            ['pid', uniqid()],
        ];
    }

    /**
     * @return Build
     */
    protected function getEntity()
    {
        return new Build($this->app, $this->type, $this->env);
    }
}
