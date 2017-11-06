<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Ssh\Auth;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth\Password;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of PasswordTest
 *
 * @author Jelle Sebreghts
 */
class PasswordTest extends TestCase
{

    use DataGenerator;

    protected $user;
    protected $passphrase;

    protected function setUp()
    {
        parent::setUp();
        $this->user = $this->getAlphaNumeric();
        $this->passphrase = $this->getAlphaNumeric();
    }

    public function testSuccessNoPassword() {
        $this->passphrase = null;

        $password = $this->getPassword();

        $connection = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $connection->expects($this->once())->method('login')->with($this->user)->willReturn(true);

        $this->assertNull($password->authenticate($connection));
    }

    public function testSuccess() {
        $password = $this->getPassword();

        $connection = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $connection->expects($this->once())->method('login')->with($this->user, $this->passphrase)->willReturn(true);

        $this->assertNull($password->authenticate($connection));
    }


    public function testFailNoPassword() {
        $this->passphrase = null;

        $password = $this->getPassword();
        $connection = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $connection->expects($this->once())->method('login')->with($this->user)->willReturn(false);

        try {
            $password->authenticate($connection);
        }
        catch (RuntimeException $e) {
            $this->assertEquals(sprintf(
                "fail: unable to authenticate user '%s', using password: NO",
                $this->user
            ), $e->getMessage());
            return;
        }
        $this->fail('No RuntimeException thrown when ssh login fails.');
    }

    public function testFail() {
        $password = $this->getPassword();
        $connection = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $connection->expects($this->once())->method('login')->with($this->user, $this->passphrase)->willReturn(false);

        try {
            $password->authenticate($connection);
        }
        catch (RuntimeException $e) {
            $this->assertEquals(sprintf(
                "fail: unable to authenticate user '%s', using password: YES",
                $this->user
            ), $e->getMessage());
            return;
        }
        $this->fail('No RuntimeException thrown when ssh login fails.');
    }

    protected function getPassword()
    {
        return new Password($this->user, $this->passphrase);
    }

}
