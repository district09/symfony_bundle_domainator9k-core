<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Ssh\Factory;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth\KeyFile;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth\Password;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshFactory;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactory;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShell;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of SshShellFactoryTest.
 *
 * @author Jelle Sebreghts
 */
class SshShellFactoryTest extends TestCase
{
    use DataGenerator;

    protected $host;
    protected $port;
    protected $timeout;

    protected function setUp()
    {
        parent::setUp();
        $this->host = $this->getAlphaNumeric();
        $this->user = $this->getAlphaNumeric();
        $this->password = $this->getAlphaNumeric();
    }

    public function testCreate()
    {
        $factory = $this->getSshShellFactory();

        $pwShell = $factory->create($this->host, SshShellFactory::AUTH_TYPE_CREDENTIALS, $this->user, $this->password);
        $this->assertInstanceOf(SshShell::class, $pwShell);
        $this->assertInstanceOf(Password::class, $pwShell->getAuth());
        $this->assertEquals($this->host, $pwShell->getHost());

        $pwShell2 = $factory->create($this->host, $this->getAlphaNumeric(), $this->user, $this->password);
        $this->assertInstanceOf(SshShell::class, $pwShell2);
        $this->assertInstanceOf(Password::class, $pwShell2->getAuth());
        $this->assertEquals($this->host, $pwShell2->getHost());

        $keyFileShell = $factory->create($this->host, SshShellFactory::AUTH_TYPE_KEY, $this->user, $this->password);
        $this->assertInstanceOf(SshShell::class, $keyFileShell);
        $this->assertInstanceOf(KeyFile::class, $keyFileShell->getAuth());
        $this->assertEquals($this->host, $keyFileShell->getHost());
    }

    protected function getSshShellFactory()
    {
        return new SshShellFactory(new SshFactory());
    }
}
