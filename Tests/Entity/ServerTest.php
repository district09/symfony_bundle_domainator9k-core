<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Role;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\EntityTest;

/**
 * Description of ServerTest
 *
 * @author Jelle Sebreghts
 */
class ServerTest extends EntityTest
{

    /**
     *
     * @var string
     */
    protected $sockId;

    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $ip;

    /**
     *
     * @var string
     */
    protected $environment;

    protected function setUp()
    {
        parent::setUp();
        $this->sockId = uniqid();
        $this->name = $this->getAlphaNumeric();
        $this->ip = $this->getAlphaNumeric();
        $this->environment = $this->getAlphaNumeric();
    }

    public function testConstructor()
    {
        $server = $this->getEntity();
        $this->assertEquals($this->sockId, $server->getSockId());
        $this->assertEquals($this->name, $server->getName());
        $this->assertEquals($this->ip, $server->getIp());
        $this->assertEquals($this->environment, $server->getEnvironment());
    }

    public function getterTestDataProvider()
    {
        return [
            ['id', uniqid()],
            ['name', $this->getAlphaNumeric()],
            ['sockId', uniqid()],
            ['manageSock', (bool) mt_rand(0, 1), true, ''],
            ['taskServer', (bool) mt_rand(0, 1), true],
            ['ip', $this->getAlphaNumeric()],
            ['environment', $this->getAlphaNumeric()],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['name', $this->getAlphaNumeric()],
            ['sockId', uniqid()],
            ['manageSock', (bool) mt_rand(0, 1), true, ''],
            ['taskServer', (bool) mt_rand(0, 1), true],
            ['ip', $this->getAlphaNumeric()],
            ['environment', $this->getAlphaNumeric()],
        ];
    }

    /**
     *
     * @return Role
     */
    protected function getEntity()
    {
        return new Server($this->sockId, $this->name, $this->ip, $this->environment);
    }

}
