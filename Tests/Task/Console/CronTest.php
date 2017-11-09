<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Task\Console;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactory;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShellInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\Console\CronTask;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of CronTest
 *
 * @author Jelle Sebreghts
 */
class CronTest extends TestCase
{

    use DataGenerator;

    protected $options = [];
    protected $sshShellFactory;
    protected $shell;

    protected function setUp()
    {
        parent::setUp();
        $this->options = [
            'cron' => $this->getAlphaNumeric(),
            'check' => true,
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
        $this->assertEquals('console.cron', CronTask::getName());
    }

    public function testExecute()
    {
        $cron = $this->getCronTask();

        $this->sshShellFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->options['host'], $this->options['authtype'], $this->options['user'], $this->options['password']
            )
            ->willReturn($this->shell);

        $cmd = '(crontab -l | grep -vE "(' . preg_quote(trim($this->options['cron'])) . ')"; echo "' . $this->options['cron'] . '") | crontab';

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

        $result = $cron->execute();

        $this->assertTrue($result->isSuccess());
        $this->assertContains(sprintf('%s installed cron job %s', 'SUCCESS', $this->options['cron']), $result->getMessages());
    }

     public function testExecuteFails()
    {
        $cron = $this->getCronTask();

        $this->sshShellFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->options['host'], $this->options['authtype'], $this->options['user'], $this->options['password']
            )
            ->willReturn($this->shell);

        $cmd = '(crontab -l | grep -vE "(' . preg_quote(trim($this->options['cron'])) . ')"; echo "' . $this->options['cron'] . '") | crontab';

        $expectedStdout = $this->getAlphaNumeric();
        $expectedExitStatus = mt_rand(1, 255);
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

        $result = $cron->execute();

        $this->assertFalse($result->isSuccess());
        $this->assertContains(sprintf('%s installed cron job %s', 'FAILED', $this->options['cron']), $result->getMessages());
        $this->assertContains($expectedStdout, $result->getMessages());
        $this->assertContains($expectedStderr, $result->getMessages());
    }

    public function testExecuteCustomCheck()
    {
        $this->options['check'] = $this->getAlphaNumeric();

        $cron = $this->getCronTask();

        $this->sshShellFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->options['host'], $this->options['authtype'], $this->options['user'], $this->options['password']
            )
            ->willReturn($this->shell);

        $cmd = '(crontab -l | grep -vE "' . $this->options['check'] . '"; echo "' . $this->options['cron'] . '") | crontab';

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

        $result = $cron->execute();

        $this->assertTrue($result->isSuccess());
        $this->assertContains(sprintf('%s installed cron job %s', 'SUCCESS', $this->options['cron']), $result->getMessages());
    }

    public function testExecuteNoCheck()
    {
        $this->options['check'] = false;

        $cron = $this->getCronTask();

        $this->sshShellFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->options['host'], $this->options['authtype'], $this->options['user'], $this->options['password']
            )
            ->willReturn($this->shell);

        $cmd = '(crontab -l ; echo "' . $this->options['cron'] . '") | crontab';

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

        $result = $cron->execute();

        $this->assertTrue($result->isSuccess());
        $this->assertContains(sprintf('%s installed cron job %s', 'SUCCESS', $this->options['cron']), $result->getMessages());
    }

    public function testKeyFile() {
        $this->options['authtype'] = SshShellFactory::AUTH_TYPE_KEY;
        $this->options['keyfile'] = $this->getAlphaNumeric();
        unset($this->options['password']);
        $cron = $this->getCronTask();

        $this->sshShellFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->options['host'], $this->options['authtype'], $this->options['user'], $this->options['keyfile']
            )
            ->willReturn($this->shell);

        $cmd = '(crontab -l | grep -vE "(' . preg_quote(trim($this->options['cron'])) . ')"; echo "' . $this->options['cron'] . '") | crontab';

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

        $result = $cron->execute();

        $this->assertTrue($result->isSuccess());
        $this->assertContains(sprintf('%s installed cron job %s', 'SUCCESS', $this->options['cron']), $result->getMessages());
    }

    public function testNoAuthTypePassword() {
        unset($this->options['authtype']);
        $cron = $this->getCronTask();

        $this->sshShellFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->options['host'], SshShellFactory::AUTH_TYPE_CREDENTIALS, $this->options['user'], $this->options['password']
            )
            ->willReturn($this->shell);

        $cmd = '(crontab -l | grep -vE "(' . preg_quote(trim($this->options['cron'])) . ')"; echo "' . $this->options['cron'] . '") | crontab';

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

        $result = $cron->execute();

        $this->assertTrue($result->isSuccess());
        $this->assertContains(sprintf('%s installed cron job %s', 'SUCCESS', $this->options['cron']), $result->getMessages());
    }

    public function testNoAuthTypeKeyFile() {
        unset($this->options['authtype']);
        unset($this->options['password']);
        $this->options['keyfile'] = $this->getAlphaNumeric();

        $cron = $this->getCronTask();

        $this->sshShellFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->options['host'], SshShellFactory::AUTH_TYPE_KEY, $this->options['user'], $this->options['keyfile']
            )
            ->willReturn($this->shell);

        $cmd = '(crontab -l | grep -vE "(' . preg_quote(trim($this->options['cron'])) . ')"; echo "' . $this->options['cron'] . '") | crontab';

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

        $result = $cron->execute();

        $this->assertTrue($result->isSuccess());
        $this->assertContains(sprintf('%s installed cron job %s', 'SUCCESS', $this->options['cron']), $result->getMessages());
    }

    protected function getCronTask()
    {
        $cron = new CronTask($this->options);
        $cron->setSshShellFactory($this->sshShellFactory);
        return $cron;
    }
}
