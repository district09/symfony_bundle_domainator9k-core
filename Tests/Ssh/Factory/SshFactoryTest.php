<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Ssh\Factory;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshFactory;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use phpseclib\Net\SFTP;
use phpseclib\Net\SSH2;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of SshFactoryTest.
 *
 * @author Jelle Sebreghts
 */
class SshFactoryTest extends TestCase
{
    use DataGenerator;

    protected $host;
    protected $port;
    protected $timeout;

    protected function setUp()
    {
        parent::setUp();
        $this->host = $this->getAlphaNumeric();
        $this->port = uniqid();
        $this->timeout = mt_rand(0, 100);
    }

    public function testGetSftpConnection()
    {
        $factory = $this->getSshFactory();

        $connection = $factory->getSftpConnection($this->host, $this->port, $this->timeout);
        $this->assertInstanceOf(SFTP::class, $connection);
        $this->assertEquals($this->host, $connection->host);
        $this->assertEquals($this->port, $connection->port);
        $this->assertEquals($this->timeout, $connection->timeout);
    }

    public function testGetSshConnection()
    {
        $factory = $this->getSshFactory();

        $connection = $factory->getSshConnection($this->host, $this->port, $this->timeout);
        $this->assertInstanceOf(SSH2::class, $connection);
        $this->assertEquals($this->host, $connection->host);
        $this->assertEquals($this->port, $connection->port);
        $this->assertEquals($this->timeout, $connection->timeout);
    }

    protected function getSshFactory()
    {
        return new SshFactory();
    }
}
