<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Task\Provision;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ServerSettings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Interfaces\ApplicationTypeInterface;
use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShellInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\FactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\Provision\CronTask;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskResult;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskRunner;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of CronTaskTest
 *
 * @author Jelle Sebreghts
 */
class CronTaskTest extends TestCase
{

    use DataGenerator;

    protected $options = [];
    protected $taskFactory;
    protected $shell;

    protected function setUp()
    {
        parent::setUp();
        $this->options = [
            'applicationTypeBuilder' => $this->getMockBuilder(ApplicationTypeBuilder::class)->disableOriginalConstructor()->getMock(),
            'appEnvironment' => $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock(),
            'servers' => [
                $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock(),
                $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock(),
            ],
        ];
        $this->taskFactory = $this->getMockBuilder(FactoryInterface::class)->getMock();
        $this->shell = $this->getMockBuilder(SshShellInterface::class)->getMock();
    }

    public function testGetName()
    {
        $this->assertEquals('provision.cron', CronTask::getName());
    }

    public function testExecute()
    {
        $task = $this->getTask();
        $home = realpath(__DIR__ . '/../../TestTools');
        $task->setHomeDirectory($home);

        $taskRunner = $this->getMockBuilder(TaskRunner::class)->getMock();
        $this->taskFactory->expects($this->once())->method('createRunner')->willReturn($taskRunner);

        $user = $this->getAlphaNumeric();
        $serverSettings = $this->getMockBuilder(ServerSettings::class)->disableOriginalConstructor()->getMock();
        $serverSettings->expects($this->once())->method('getUser')->willReturn($user);

        $folder = $this->getAlphaNumeric();
        $cron = $this->getAlphaNumeric();
        $application = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $application->expects($this->once())->method('getNameForFolder')->willReturn($folder);
        $application->expects($this->once())->method('getCron')->willReturn('__APP__' . $cron);

        $this->options['appEnvironment']->expects($this->once())->method('getServerSettings')->willReturn($serverSettings);
        $this->options['appEnvironment']->expects($this->once())->method('getApplication')->willReturn($application);

        $ip = $this->getAlphaNumeric();
        $this->options['servers'][0]->expects($this->once())->method('isTaskServer')->willReturn(true);
        $this->options['servers'][0]->expects($this->once())->method('getIp')->willReturn($ip);


        $this->options['servers'][1]->expects($this->once())->method('isTaskServer')->willReturn(false);
        $this->options['servers'][1]->expects($this->never())->method('getIp');

        $cronTask = $this->getMockBuilder(TaskInterface::class)->getMock();

        $this->taskFactory->expects($this->once())->method('create')->with(
            'console.cron',
            [
                'appEnvironment' => $this->options['appEnvironment'],
                'host' => $ip,
                'user' => $user,
                'keyfile' => realpath($home . '/.ssh/id_rsa'),
                'cron' => "/home/$user/apps/$folder/current" . $cron,
                'check' => true,
            ]
        )->willReturn($cronTask);

        $result = $this->getMockBuilder(TaskResult::class)->disableOriginalConstructor()->getMock();

        $taskRunner->expects($this->once())->method('addTask')->with($cronTask);
        $taskRunner->expects($this->once())->method('run')->willReturn($result);

        $this->assertEquals($result, $task->execute());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testNoKeyFile() {
        $task = $this->getTask();
        $home = $this->getAlphaNumeric();
        $task->setHomeDirectory($home);

        $taskRunner = $this->getMockBuilder(TaskRunner::class)->getMock();
        $this->taskFactory->expects($this->once())->method('createRunner')->willReturn($taskRunner);

        $user = $this->getAlphaNumeric();
        $serverSettings = $this->getMockBuilder(ServerSettings::class)->disableOriginalConstructor()->getMock();
        $serverSettings->expects($this->once())->method('getUser')->willReturn($user);

        $folder = $this->getAlphaNumeric();
        $application = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $application->expects($this->once())->method('getNameForFolder')->willReturn($folder);

        $this->options['appEnvironment']->expects($this->once())->method('getServerSettings')->willReturn($serverSettings);
        $this->options['appEnvironment']->expects($this->once())->method('getApplication')->willReturn($application);

        $task->execute();

    }

    protected function getTask()
    {
        $task = new CronTask($this->options);
        $task->setTaskFactory($this->taskFactory);
        return $task;
    }
}
