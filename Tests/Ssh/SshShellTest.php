<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Ssh;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth\AbstractAuth;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshFactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShell;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\EntityTest;
use phpseclib\Net\SFTP;
use phpseclib\Net\SSH2;

if (!defined('NET_SFTP_TYPE_REGULAR')) {
    define('NET_SFTP_TYPE_REGULAR', 1);
}
if (!defined('NET_SFTP_TYPE_DIRECTORY')) {
    define('NET_SFTP_TYPE_DIRECTORY', 2);
}

/**
 * Description of SshShellTest.
 *
 * @author Jelle Sebreghts
 */
class SshShellTest extends EntityTest
{
    protected $host;
    protected $auth;
    protected $sshFactory;

    protected function setUp()
    {
        parent::setUp();
        $this->host = $this->getAlphaNumeric();
        $this->auth = $this->getMockBuilder(AbstractAuth::class)->disableOriginalConstructor()->getMock();
        $this->sshFactory = $this->getMockBuilder(SshFactoryInterface::class)->getMock();
    }

    public function testConnect()
    {
        $shell = $this->getShell();

        $ssh = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $ssh->expects($this->once())->method('_connect');
        $ssh->expects($this->once())->method('isConnected')->willReturn(true);

        $this->auth->expects($this->once())->method('authenticate')->with($ssh);

        $this->sshFactory->expects($this->once())->method('getSshConnection')->with($this->host, 22, 10)->willReturn($ssh);

        $this->assertNull($shell->connect());
        // Connectng a second time should not create a new connection.
        $this->assertNull($shell->connect());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testConnectFail()
    {
        $shell = $this->getShell();

        $ssh = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $ssh->expects($this->once())->method('_connect');
        $ssh->expects($this->once())->method('isConnected')->willReturn(false);

        $this->auth->expects($this->never())->method('authenticate')->with($ssh);

        $this->sshFactory->expects($this->once())->method('getSshConnection')->with($this->host, 22, 10)->willReturn($ssh);
        $shell->connect();
    }

    public function testGetSftp()
    {
        $shell = $this->getShell();

        $ssh = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $ssh->expects($this->once())->method('_connect');
        $ssh->expects($this->once())->method('isConnected')->willReturn(true);

        $sftp = $this->getMockBuilder(SFTP::class)->disableOriginalConstructor()->getMock();

        $this->auth->expects($this->at(0))->method('authenticate')->with($ssh);
        $this->auth->expects($this->at(1))->method('authenticate')->with($sftp);

        $this->sshFactory->expects($this->once())->method('getSshConnection')->with($this->host, 22, 10)->willReturn($ssh);
        $this->sshFactory->expects($this->once())->method('getSftpConnection')->with($this->host, 22, 10)->willReturn($sftp);

        $shell->getSFtp();
    }

    public function testDisconnect()
    {
        $shell = $this->getShell();

        $ssh = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $ssh->expects($this->once())->method('_connect');
        $ssh->expects($this->once())->method('isConnected')->willReturn(true);
        $ssh->expects($this->once())->method('disconnect');

        $this->auth->expects($this->at(0))->method('authenticate')->with($ssh);

        $this->sshFactory->expects($this->once())->method('getSshConnection')->with($this->host, 22, 10)->willReturn($ssh);

        $shell->disconnect();
    }

    public function testExec()
    {
        $shell = $this->getShell();

        $command = $this->getAlphaNumeric();
        $expectedStdOut = $this->getAlphaNumeric();
        $expectedStdErr = $this->getAlphaNumeric();
        $expectedExitStatus = 0;

        $stdOut = $exitStatus = $stdErr = null;

        $ssh = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $ssh->expects($this->once())->method('_connect');
        $ssh->expects($this->once())->method('isConnected')->willReturn(true);
        $ssh->expects($this->once())->method('exec')->with($command)->willReturn($expectedStdOut);
        $ssh->expects($this->once())->method('getStdError')->willReturn($expectedStdErr);
        $ssh->expects($this->once())->method('getExitStatus')->willReturn($expectedExitStatus);

        $this->auth->expects($this->at(0))->method('authenticate')->with($ssh);

        $this->sshFactory->expects($this->once())->method('getSshConnection')->with($this->host, 22, 10)->willReturn($ssh);

        $this->assertTrue($shell->exec($command, $stdOut, $exitStatus, $stdErr));
        $this->assertEquals($expectedExitStatus, $exitStatus);
        $this->assertEquals($expectedStdOut, $stdOut);
        $this->assertEquals($expectedStdErr, $stdErr);
    }

    public function testExecFail()
    {
        $shell = $this->getShell();

        $command = $this->getAlphaNumeric();
        $expectedStdOut = $this->getAlphaNumeric();
        $expectedStdErr = $this->getAlphaNumeric();
        $expectedExitStatus = mt_rand(1, 255);

        $stdOut = $exitStatus = $stdErr = null;

        $ssh = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $ssh->expects($this->once())->method('_connect');
        $ssh->expects($this->once())->method('isConnected')->willReturn(true);
        $ssh->expects($this->once())->method('exec')->with($command)->willReturn($expectedStdOut);
        $ssh->expects($this->once())->method('getStdError')->willReturn($expectedStdErr);
        $ssh->expects($this->once())->method('getExitStatus')->willReturn($expectedExitStatus);

        $this->auth->expects($this->at(0))->method('authenticate')->with($ssh);

        $this->sshFactory->expects($this->once())->method('getSshConnection')->with($this->host, 22, 10)->willReturn($ssh);

        $this->assertFalse($shell->exec($command, $stdOut, $exitStatus, $stdErr));
        $this->assertEquals($expectedExitStatus, $exitStatus);
        $this->assertEquals($expectedStdOut, $stdOut);
        $this->assertEquals($expectedStdErr, $stdErr);
    }

    public function testStatFile()
    {
        $shell = $this->getShell();

        $file = $this->getAlphaNumeric();

        $ssh = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $ssh->expects($this->once())->method('_connect');
        $ssh->expects($this->once())->method('isConnected')->willReturn(true);

        $sftp = $this->getMockBuilder(SFTP::class)->disableOriginalConstructor()->getMock();
        $sftp->expects($this->once())->method('stat')->with($file)->willReturn(['type' => NET_SFTP_TYPE_REGULAR]);

        $this->auth->expects($this->at(0))->method('authenticate')->with($ssh);
        $this->auth->expects($this->at(1))->method('authenticate')->with($sftp);

        $this->sshFactory->expects($this->once())->method('getSshConnection')->with($this->host, 22, 10)->willReturn($ssh);
        $this->sshFactory->expects($this->once())->method('getSftpConnection')->with($this->host, 22, 10)->willReturn($sftp);

        $this->assertEquals(['type' => 'file'], $shell->stat($file));
    }

    public function testStatDir()
    {
        $shell = $this->getShell();

        $file = $this->getAlphaNumeric();

        $ssh = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $ssh->expects($this->once())->method('_connect');
        $ssh->expects($this->once())->method('isConnected')->willReturn(true);

        $sftp = $this->getMockBuilder(SFTP::class)->disableOriginalConstructor()->getMock();
        $sftp->expects($this->once())->method('stat')->with($file)->willReturn(['type' => NET_SFTP_TYPE_DIRECTORY]);

        $this->auth->expects($this->at(0))->method('authenticate')->with($ssh);
        $this->auth->expects($this->at(1))->method('authenticate')->with($sftp);

        $this->sshFactory->expects($this->once())->method('getSshConnection')->with($this->host, 22, 10)->willReturn($ssh);
        $this->sshFactory->expects($this->once())->method('getSftpConnection')->with($this->host, 22, 10)->willReturn($sftp);

        $this->assertEquals(['type' => 'dir'], $shell->stat($file));
    }

    public function testStatFail()
    {
        $shell = $this->getShell();

        $file = $this->getAlphaNumeric();

        $ssh = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $ssh->expects($this->once())->method('_connect');
        $ssh->expects($this->once())->method('isConnected')->willReturn(true);

        $sftp = $this->getMockBuilder(SFTP::class)->disableOriginalConstructor()->getMock();
        $sftp->expects($this->once())->method('stat')->with($file)->willReturn(false);

        $this->auth->expects($this->at(0))->method('authenticate')->with($ssh);
        $this->auth->expects($this->at(1))->method('authenticate')->with($sftp);

        $this->sshFactory->expects($this->once())->method('getSshConnection')->with($this->host, 22, 10)->willReturn($ssh);
        $this->sshFactory->expects($this->once())->method('getSftpConnection')->with($this->host, 22, 10)->willReturn($sftp);

        $this->assertFalse($shell->stat($file));
    }

    public function testFileExists()
    {
        $shell = $this->getShell();

        $file1 = $this->getAlphaNumeric();
        $file2 = $this->getAlphaNumeric();

        $ssh = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $ssh->expects($this->once())->method('_connect');
        $ssh->expects($this->once())->method('isConnected')->willReturn(true);

        $sftp = $this->getMockBuilder(SFTP::class)->disableOriginalConstructor()->getMock();
        $sftp->expects($this->at(0))->method('file_exists')->with($file1)->willReturn(true);
        $sftp->expects($this->at(1))->method('file_exists')->with($file2)->willReturn(false);

        $this->auth->expects($this->at(0))->method('authenticate')->with($ssh);
        $this->auth->expects($this->at(1))->method('authenticate')->with($sftp);

        $this->sshFactory->expects($this->once())->method('getSshConnection')->with($this->host, 22, 10)->willReturn($ssh);
        $this->sshFactory->expects($this->once())->method('getSftpConnection')->with($this->host, 22, 10)->willReturn($sftp);

        $this->assertTrue($shell->fileExists($file1));
        $this->assertFalse($shell->fileExists($file2));
    }

    public function testFilePutContent()
    {
        $shell = $this->getShell();

        $file1 = $this->getAlphaNumeric();
        $file2 = $this->getAlphaNumeric();
        $content = $this->getAlphaNumeric();

        $ssh = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $ssh->expects($this->once())->method('_connect');
        $ssh->expects($this->once())->method('isConnected')->willReturn(true);

        $sftp = $this->getMockBuilder(SFTP::class)->disableOriginalConstructor()->getMock();
        $sftp->expects($this->at(0))->method('put')->with($file1, $content, SFTP::SOURCE_STRING, -1, -1, null)->willReturn(true);
        $sftp->expects($this->at(1))->method('put')->with($file2, $content, SFTP::SOURCE_STRING, -1, -1, null)->willReturn(false);

        $this->auth->expects($this->at(0))->method('authenticate')->with($ssh);
        $this->auth->expects($this->at(1))->method('authenticate')->with($sftp);

        $this->sshFactory->expects($this->once())->method('getSshConnection')->with($this->host, 22, 10)->willReturn($ssh);
        $this->sshFactory->expects($this->once())->method('getSftpConnection')->with($this->host, 22, 10)->willReturn($sftp);

        $this->assertTrue($shell->filePutContent($file1, $content));
        $this->assertFalse($shell->filePutContent($file2, $content));
    }

    public function testMkdir()
    {
        $shell = $this->getShell();

        $dir1 = $this->getAlphaNumeric();
        $dir2 = $this->getAlphaNumeric();

        $ssh = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $ssh->expects($this->once())->method('_connect');
        $ssh->expects($this->once())->method('isConnected')->willReturn(true);

        $sftp = $this->getMockBuilder(SFTP::class)->disableOriginalConstructor()->getMock();
        $sftp->expects($this->at(0))->method('mkdir')->with($dir1, 0777, false)->willReturn(true);
        $sftp->expects($this->at(1))->method('mkdir')->with($dir2, 0777, false)->willReturn(false);

        $this->auth->expects($this->at(0))->method('authenticate')->with($ssh);
        $this->auth->expects($this->at(1))->method('authenticate')->with($sftp);

        $this->sshFactory->expects($this->once())->method('getSshConnection')->with($this->host, 22, 10)->willReturn($ssh);
        $this->sshFactory->expects($this->once())->method('getSftpConnection')->with($this->host, 22, 10)->willReturn($sftp);

        $this->assertTrue($shell->mkdir($dir1, 0777, false));
        $this->assertFalse($shell->mkdir($dir2, 0777, false));
    }

    public function testChmod()
    {
        $shell = $this->getShell();

        $dir1 = $this->getAlphaNumeric();
        $dir2 = $this->getAlphaNumeric();

        $ssh = $this->getMockBuilder(SSH2::class)->disableOriginalConstructor()->getMock();
        $ssh->expects($this->once())->method('_connect');
        $ssh->expects($this->once())->method('isConnected')->willReturn(true);

        $sftp = $this->getMockBuilder(SFTP::class)->disableOriginalConstructor()->getMock();
        $sftp->expects($this->at(0))->method('chmod')->with($dir1, 0777)->willReturn(true);
        $sftp->expects($this->at(1))->method('chmod')->with($dir2, 0777)->willReturn(false);

        $this->auth->expects($this->at(0))->method('authenticate')->with($ssh);
        $this->auth->expects($this->at(1))->method('authenticate')->with($sftp);

        $this->sshFactory->expects($this->once())->method('getSshConnection')->with($this->host, 22, 10)->willReturn($ssh);
        $this->sshFactory->expects($this->once())->method('getSftpConnection')->with($this->host, 22, 10)->willReturn($sftp);

        $this->assertTrue($shell->chmod(0777, $dir1));
        $this->assertFalse($shell->chmod(0777, $dir2));
    }

    public function getterTestDataProvider()
    {
        return [
            ['host', $this->getAlphaNumeric()],
            ['port', uniqid()],
            ['auth', $this->getMockBuilder(AbstractAuth::class)->disableOriginalConstructor()->getMock()],
            ['timeout', uniqid()],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['host', $this->getAlphaNumeric()],
            ['port', uniqid()],
            ['auth', $this->getMockBuilder(AbstractAuth::class)->disableOriginalConstructor()->getMock()],
            ['timeout', uniqid()],
        ];
    }

    protected function getEntity()
    {
        return $this->getShell();
    }

    protected function getShell()
    {
        return new SshShell($this->host, $this->auth, $this->sshFactory);
    }
}
