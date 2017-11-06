<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Ssh\Auth;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth\None;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use phpseclib\Net\SSH2;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of NoneTest
 *
 * @author Jelle Sebreghts
 */
class NoneTest extends TestCase
{

    use DataGenerator;

    protected $user;

    protected function setUp()
    {
        parent::setUp();
        $this->user = $this->getAlphaNumeric();
    }

    public function testSuccess() {
        $none = $this->getNone();

        $connection = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $connection->expects($this->once())->method('login')->with($this->user)->willReturn(true);

        $this->assertNull($none->authenticate($connection));
    }


    public function testFail() {
        $none = $this->getNone();

        $connection = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $connection->expects($this->once())->method('login')->with($this->user)->willReturn(false);

        try {
            $none->authenticate($connection);
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

    protected function getNone()
    {
        return new None($this->user);
    }

}
