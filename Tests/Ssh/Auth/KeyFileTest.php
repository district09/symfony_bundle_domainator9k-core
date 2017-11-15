<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Ssh\Auth;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth\KeyFile;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of KeyFileTest.
 *
 * @author Jelle Sebreghts
 */
class KeyFileTest extends TestCase
{
    use DataGenerator;

    protected $user;
    protected $privateKeyFile;
    protected $passphrase;

    protected function setUp()
    {
        parent::setUp();
        $this->user = $this->getAlphaNumeric();
        $this->privateKeyFile = __DIR__ . '/key';
        $this->passphrase = $this->getAlphaNumeric();
    }

    public function testSuccess()
    {
        $keyFile = $this->getKeyFile();

        $connection = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $connection->expects($this->once())->method('login')->with($this->user, $this->callback(function (RSA $rsa) {
            // Type hinting checks the object type.
            return true;
        }))->willReturn(true);

        $this->assertNull($keyFile->authenticate($connection));
    }

    public function testFail()
    {
        $keyFile = $this->getKeyFile();

        $connection = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $connection->expects($this->once())->method('login')->with($this->user, $this->callback(function (RSA $rsa) {
            // Type hinting checks the object type.
            return true;
        }))->willReturn(false);

        try {
            $keyFile->authenticate($connection);
        } catch (\RuntimeException $e) {
            $this->assertEquals(sprintf(
                "fail: unable to authenticate user '%s' using key file",
                $this->user
            ), $e->getMessage());

            return;
        }
        $this->fail('No RuntimeException thrown when ssh login fails.');
    }

    protected function getKeyFile()
    {
        return new KeyFile($this->user, $this->privateKeyFile, $this->passphrase);
    }
}
