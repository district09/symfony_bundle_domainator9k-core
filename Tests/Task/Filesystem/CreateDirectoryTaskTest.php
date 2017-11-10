<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Task\Console;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactory;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShellInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\Filesystem\CreateDirectoryTask;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of CreateDirectoryTaskTest
 *
 * @author Jelle Sebreghts
 */
class CreateDirectoryTaskTest extends TestCase
{

    use DataGenerator;

    protected $options = [];
    protected $sshShellFactory;
    protected $shell;

    protected function setUp()
    {
        parent::setUp();
        $this->options = [
            'directory' => $this->getAlphaNumeric(),
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
        $this->assertEquals('filesystem.create_directory', CreateDirectoryTask::getName());
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

        $this->shell
            ->expects($this->once())
            ->method('fileExists')
            ->with($this->options['directory'])
            ->willReturn(false);

        $this->shell->expects($this->once())->method('mkdir')->with($this->options['directory'], 0755, true)->willReturn(true);

        $result = $task->execute();

        $this->assertTrue($result->isSuccess());
        $this->assertContains(sprintf('creating directory %s', $this->options['directory']), $result->getMessages());
        $this->assertContains(sprintf('SUCCESS creating directory %s', $this->options['directory']), $result->getMessages());
    }

    public function testDirExists()
    {
        $task = $this->getTask();

        $this->sshShellFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->options['host'], $this->options['authtype'], $this->options['user'], $this->options['password']
            )
            ->willReturn($this->shell);

        $this->shell
            ->expects($this->once())
            ->method('fileExists')
            ->with($this->options['directory'])
            ->willReturn(true);

        $this->shell->expects($this->never())->method('mkdir');

        $result = $task->execute();

        $this->assertTrue($result->isSuccess());
        $this->assertContains(sprintf('SUCCESS creating directory %s, directory already exists', $this->options['directory']), $result->getMessages());
    }

    public function testExecuteFails()
    {
        $task = $this->getTask();

        $this->sshShellFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->options['host'], $this->options['authtype'], $this->options['user'], $this->options['password']
            )
            ->willReturn($this->shell);

        $this->shell
            ->expects($this->once())
            ->method('fileExists')
            ->with($this->options['directory'])
            ->willReturn(false);

        $this->shell->expects($this->once())->method('mkdir')->with($this->options['directory'], 0755, true)->willReturn(false);

        $result = $task->execute();

        $this->assertFalse($result->isSuccess());
        $this->assertContains(sprintf('FAILED creating directory %s', $this->options['directory']), $result->getMessages());
    }

    protected function getTask()
    {
        $task = new CreateDirectoryTask($this->options);
        $task->setSshShellFactory($this->sshShellFactory);
        return $task;
    }
}
