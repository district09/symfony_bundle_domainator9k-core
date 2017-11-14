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
use DigipolisGent\Domainator9k\CoreBundle\Task\Provision\FilesystemTask;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskResult;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskRunner;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Webmozart\PathUtil\Path;

/**
 * Description of FilesystemTaskTest
 *
 * @author Jelle Sebreghts
 */
class FilesystemTaskTest extends TestCase
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
        $this->assertEquals('provision.filesystem', FilesystemTask::getName());
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

        $typeSlug = $this->getAlphaNumeric();$folder = $this->getAlphaNumeric();
        $application = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $application->expects($this->once())->method('getAppTypeSlug')->willReturn($typeSlug);
        $application->expects($this->once())->method('getNameForFolder')->willReturn($folder);

        $this->options['appEnvironment']->expects($this->once())->method('getServerSettings')->willReturn($serverSettings);
        $this->options['appEnvironment']->expects($this->once())->method('getApplication')->willReturn($application);

        $userFolder = $this->getAlphaNumeric();
        $appType = $this->getMockBuilder(ApplicationTypeInterface::class)->getMock();
        $appType->expects($this->once())->method('getDirectories')->with($user)->willReturn([$userFolder]);

        $this->options['applicationTypeBuilder']->expects($this->once())->method('getType')->with($typeSlug)->willReturn($appType);

        $ip = $this->getAlphaNumeric();
        $this->options['servers'][0]->expects($this->once())->method('getIp')->willReturn($ip);

        $createFileTask = $this->getMockBuilder(TaskInterface::class)->getMock();

        $directories = [
            "/dist/$user/$folder/files/public",
            "/dist/$user/$folder/files/private",
            "/dist/$user/$folder/config",
            "/home/$user/apps/$folder/releases",
            "/home/$user/apps/$folder/backups",
            "/home/$user/apps/$folder/files/tmp",
            $userFolder,
        ];
        $i = 1;

        foreach ($directories as $dir) {
            $this->taskFactory->expects($this->at($i++))->method('create')->with(
                'filesystem.create_directory',
                [
                    'appEnvironment' => $this->options['appEnvironment'],
                    'host' => $ip,
                    'user' => $user,
                    'keyfile' => realpath($home . '/.ssh/id_rsa'),
                    'directory' => $dir,
                ]
            )->willReturn($createFileTask);
        }

        $links = [
            "/home/$user/apps/$folder/files/public" => "/dist/$user/$folder/files/public",
            "/home/$user/apps/$folder/files/private" => "/dist/$user/$folder/files/private",
            "/home/$user/apps/$folder/config" => "/dist/$user/$folder/config",
            "/home/$user/apps/$folder/current" => "/home/$user/apps/$folder/releases/current",
        ];

        foreach ($links as $name => $target) {
            $this->taskFactory->expects($this->at($i++))->method('create')->with(
                'filesystem.link',
                [
                    'appEnvironment' => $this->options['appEnvironment'],
                    'host' => $ip,
                    'user' => $user,
                    'keyfile' => realpath($home . '/.ssh/id_rsa'),
                    'name' => $name,
                    'target' => $target,
                ]
            )->willReturn($createFileTask);
        }

        $result = $this->getMockBuilder(TaskResult::class)->disableOriginalConstructor()->getMock();

        $taskRunner->expects($this->exactly(count($directories) + count($links)))->method('addTask')->with($createFileTask);
        $taskRunner->expects($this->once())->method('run')->willReturn($result);

        $this->assertEquals($result, $task->execute());
        $this->assertTrue($task->isExecuted());
        $this->assertEquals($this->options['appEnvironment'], $task->getAppEnvironment());
        $this->assertTrue($task->revert());
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
        $folder = $this->getAlphaNumeric();
        $serverSettings = $this->getMockBuilder(ServerSettings::class)->disableOriginalConstructor()->getMock();
        $serverSettings->expects($this->once())->method('getUser')->willReturn($user);
        $application = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $application->expects($this->once())->method('getNameForFolder')->willReturn($folder);

        $this->options['appEnvironment']->expects($this->once())->method('getServerSettings')->willReturn($serverSettings);
        $this->options['appEnvironment']->expects($this->once())->method('getApplication')->willReturn($application);

        $task->execute();

    }

    public function testGetHomeDirectory() {
        $task = $this->getTask();
        $this->assertEquals(Path::getHomeDirectory(), $task->getHomeDirectory());
    }

    protected function getTask()
    {
        $task = new FilesystemTask($this->options);
        $task->setTaskFactory($this->taskFactory);
        return $task;
    }
}
