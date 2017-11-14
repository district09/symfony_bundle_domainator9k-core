<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Task\Filesystem;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactory;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShellInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\Filesystem\LinkTask;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of LinkTaskTest
 *
 * @author Jelle Sebreghts
 */
class LinkTaskTest extends TestCase
{

    use DataGenerator;

    protected $options = [];
    protected $sshShellFactory;
    protected $shell;

    protected function setUp()
    {
        parent::setUp();
        $this->options = [
            'name' => $this->getAlphaNumeric(),
            'target' => $this->getAlphaNumeric(),
            'host' => $this->getAlphaNumeric(),
            'password' => $this->getAlphaNumeric(),
            'user' => $this->getAlphaNumeric(),
            'authtype' => SshShellFactory::AUTH_TYPE_CREDENTIALS,
            'appEnvironment' => $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock(),
        ];
        $this->sshShellFactory = $this->getMockBuilder(SshShellFactoryInterface::class)->getMock();
        $this->shell = $this->getMockBuilder(SshShellInterface::class)->getMock();
    }

    public function testGetName()
    {
        $this->assertEquals('filesystem.link', LinkTask::getName());
    }

    public function testExecute()
    {
        $task = $this->getTask();

        $this->sshShellFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->options['host'], $this->options['authtype'], $this->options['user'], $this->options['password']
            )
            ->willReturn($this->shell);

        $cmd = 'ln -sfn ' . escapeshellarg($this->options['target']) . ' ' . escapeshellarg($this->options['name']);

        $expectedStdout = $this->getAlphaNumeric();
        $expectedExitStatus = 0;
        $expectedStderr = $this->getAlphaNumeric();

        $this->shell
            ->expects($this->once())
            ->method('exec')
            ->with($cmd, null, null, null)
            ->willReturnCallback(function($cmd, &$stdout, &$exitStatus, &$stderr) use ($expectedStdout, $expectedExitStatus, $expectedStderr)
            {
                $stdout = $expectedStdout;
                $stderr = $expectedStderr;
                $exitStatus = $expectedExitStatus;
                return true;
            });

        $result = $task->execute();

        $this->assertTrue($result->isSuccess());
        $this->assertContains(sprintf('SUCCESS linking from %s to %s', escapeshellarg($this->options['name']), escapeshellarg($this->options['target'])), $result->getMessages());
    }

    protected function getTask()
    {
        $task = new LinkTask($this->options);
        $task->setSshShellFactory($this->sshShellFactory);
        return $task;
    }
}
