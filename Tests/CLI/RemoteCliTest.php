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
        $command = 'some command';
        $output = 'some output';
        $connection
            ->expects($this->at(0))
            ->method('exec')
            ->with('cd -P ' . $dir);
        $connection
            ->expects($this->at(1))
            ->method('getExitStatus')
            ->willReturn(0);
        $connection
            ->expects($this->at(2))
            ->method('exec')
            ->with($command)
            ->willReturn($output);
        $connection
            ->expects($this->at(3))
            ->method('getExitStatus')
            ->willReturn(0);
        $cli = new RemoteCli($connection, '/some/dir');
        $this->assertEquals(true, $cli->execute($command));
        $this->assertEquals($output, $cli->getLastOutput());
    }
}
