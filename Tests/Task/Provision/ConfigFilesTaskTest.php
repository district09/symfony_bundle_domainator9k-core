<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Task\Console;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ServerSettings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Interfaces\ApplicationTypeInterface;
use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShellInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\FactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\Provision\ConfigFilesTask;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskResult;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskRunner;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of ConfigFilesTaskTest
 *
 * @author Jelle Sebreghts
 */
class ConfigFilesTaskTest extends TestCase
{

    use DataGenerator;

    protected $options = [];
    protected $taskFactory;
    protected $shell;

    protected function setUp()
    {
        parent::setUp();
        $this->options = [
            'settings' => $this->getMockBuilder(Settings::class)->disableOriginalConstructor()->getMock(),
            'applicationTypeBuilder' => $this->getMockBuilder(ApplicationTypeBuilder::class)->disableOriginalConstructor()->getMock(),
            'appEnvironment' => $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock(),
            'servers' => [
                $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock(),
            ],
        ];
        $this->taskFactory = $this->getMockBuilder(FactoryInterface::class)->getMock();
        $this->shell = $this->getMockBuilder(SshShellInterface::class)->getMock();
    }

    public function testGetName()
    {
        $this->assertEquals('provision.config_files', ConfigFilesTask::getName());
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

        $typeSlug = $this->getAlphaNumeric();
        $application = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $application->expects($this->once())->method('getAppTypeSlug')->willReturn($typeSlug);

        $this->options['appEnvironment']->expects($this->once())->method('getServerSettings')->willReturn($serverSettings);
        $this->options['appEnvironment']->expects($this->once())->method('getApplication')->willReturn($application);

        $configFilePath = $this->getAlphaNumeric();
        $configFileContent = $this->getAlphaNumeric();
        $appType = $this->getMockBuilder(ApplicationTypeInterface::class)->getMock();
        $appType->expects($this->once())->method('getConfigFiles')->willReturn([$configFilePath => $configFileContent]);

        $this->options['applicationTypeBuilder']->expects($this->once())->method('getType')->with($typeSlug)->willReturn($appType);

        $ip = $this->getAlphaNumeric();
        $this->options['servers'][0]->expects($this->once())->method('getIp')->willReturn($ip);

        $createFileTask = $this->getMockBuilder(TaskInterface::class)->getMock();

        $this->taskFactory->expects($this->once())->method('create')->with(
            'filesystem.create_file',
            [
                'appEnvironment' => $this->options['appEnvironment'],
                'host' => $ip,
                'user' => $user,
                'keyfile' => realpath($home . '/.ssh/id_rsa'),
                'path' => $configFilePath,
                'content' => $configFileContent,
            ]
        )->willReturn($createFileTask);

        $result = $this->getMockBuilder(TaskResult::class)->disableOriginalConstructor()->getMock();

        $taskRunner->expects($this->once())->method('addTask')->with($createFileTask);
        $taskRunner->expects($this->once())->method('run')->willReturn($result);

        $this->assertEquals($result, $task->execute());
    }

    /**
     * @expectedException \RuntimeException
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
        $this->options['appEnvironment']->expects($this->once())->method('getServerSettings')->willReturn($serverSettings);

        $task->execute();

    }

    protected function getTask()
    {
        $task = new ConfigFilesTask($this->options);
        $task->setTaskFactory($this->taskFactory);
        return $task;
    }
}
