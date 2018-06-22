<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\CLI;

use DigipolisGent\CommandBuilder\CommandBuilder;
use DigipolisGent\Domainator9k\CoreBundle\CLI\RemoteCli;
use phpseclib\Net\SSH2;
use PHPUnit\Framework\TestCase;

class RemoteCliTest extends TestCase
{
    public function testExecute()
    {
        $connection = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $dir = '/some/dir';
        $execute = 'some command';
        $output = 'some output';
        $connection
            ->expects($this->at(0))
            ->method('exec')
            ->with(CommandBuilder::create('cd')->addFlag('P')->addArgument($dir)->getCommand());
        $connection
            ->expects($this->at(1))
            ->method('getExitStatus')
            ->willReturn(0);
        $connection
            ->expects($this->at(2))
            ->method('exec')
            ->with(CommandBuilder::create($execute)->getCommand())
            ->willReturn($output);
        $connection
            ->expects($this->at(3))
            ->method('getExitStatus')
            ->willReturn(0);
        $cli = new RemoteCli($connection, $dir);
        $this->assertEquals(true, $cli->execute(CommandBuilder::create($execute)));
        $this->assertEquals($output, $cli->getLastOutput());
    }
}
