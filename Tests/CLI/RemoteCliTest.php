<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\CLI;

use DigipolisGent\Domainator9k\CoreBundle\CLI\RemoteCli;
use phpseclib\Net\SSH2;
use PHPUnit\Framework\TestCase;

class RemoteCliTest extends TestCase
{


    public function testExecute()
    {
        $connection = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $dir = escapeshellarg('/some/dir');
        $connection->expects($this->at(0))->method('exec')->with('cd -P ' . $dir);
        $connection->expects($this->at(1))->method('exec')->with('some command');
        $cli = new RemoteCli($connection, '/some/dir');
        $cli->execute('some command');
    }
}
