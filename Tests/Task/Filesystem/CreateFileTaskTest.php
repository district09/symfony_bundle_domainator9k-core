<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Task\Console;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactory;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShellInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\Filesystem\CreateFileTask;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of CreateFileTaskTest
 *
 * @author Jelle Sebreghts
 */
class CreateFileTaskTest extends TestCase
{

    use DataGenerator;

    protected $options = [];
    protected $sshShellFactory;
    protected $shell;

    protected function setUp()
    {
        parent::setUp();
        $this->options = [
            'path' => $this->getAlphaNumeric(),
            'content' => $this->getAlphaNumeric(),
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
        $this->assertEquals('filesystem.create_file', CreateFileTask::getName());
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

        $cmd = 'echo ' . escapeshellarg($this->options['content']) . ' > ' . escapeshellarg($this->options['path']);

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
        $this->assertContains(sprintf('SUCCESS creating %s', escapeshellarg($this->options['path'])), $result->getMessages());
    }

    protected function getTask()
    {
        $task = new CreateFileTask($this->options);
        $task->setSshShellFactory($this->sshShellFactory);
        return $task;
    }
}
