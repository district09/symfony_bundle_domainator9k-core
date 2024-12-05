<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase
{

    public function testGetSettingImplementationName()
    {
        $this->assertEquals('server',VirtualServer::getSettingImplementationName());
    }

    public function testGettersAndSetters()
    {
        $server = new VirtualServer();

        $server->setHost('192.168.1.1');
        $this->assertEquals('192.168.1.1',$server->getHost());

        $server->setPort(80);
        $this->assertEquals(80,$server->getPort());

        $environment = new Environment();
        $server->setEnvironment($environment);
        $this->assertEquals($environment,$server->getEnvironment());

        $server->setName('server-name');
        $this->assertEquals('server-name',$server->getName());

        $this->assertNull($server->isTaskServer());
        $server->setTaskServer(true);
        $this->assertTrue($server->isTaskServer());
    }
}
