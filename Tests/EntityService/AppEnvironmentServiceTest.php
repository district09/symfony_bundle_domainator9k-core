<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\EntityService;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\DatabaseSettings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ServerSettings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\EntityService\AppEnvironmentService;
use DigipolisGent\Domainator9k\CoreBundle\Interfaces\ApplicationTypeInterface;
use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskResult;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskRunnerInterface;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Stub\TaskFactory\TestTaskFactory;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use DigipolisGent\SockAPIBundle\JsonModel\Account;
use DigipolisGent\SockAPIBundle\JsonModel\Application as SockApplication;
use DigipolisGent\SockAPIBundle\JsonModel\Database;
use DigipolisGent\SockAPIBundle\Service\AccountService;
use DigipolisGent\SockAPIBundle\Service\ApplicationService;
use DigipolisGent\SockAPIBundle\Service\DatabaseService;
use DigipolisGent\SockAPIBundle\Service\Promise\EntityCreatePromise;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of AppEnvironmentServiceTest.
 *
 * @author Jelle Sebreghts
 */
class AppEnvironmentServiceTest extends TestCase
{
    use DataGenerator;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $settings;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $applicationTypeBuilder;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $taskFactory;

    protected function setUp()
    {
        parent::setUp();
        $this->settings = $this->getMockBuilder(Settings::class)->disableOriginalConstructor()->getMock();
        $this->applicationTypeBuilder = $this->getMockBuilder(ApplicationTypeBuilder::class)->getMock();
        $this->taskFactory = new TestTaskFactory();
    }

    public function testGetEntityClass()
    {
        $service = $this->getService();
        $this->assertEquals(AppEnvironment::class, $service->getEntityClass());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateSockAccountNoSockId()
    {
        $service = $this->getService();
        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $server = $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock();
        $server->expects($this->once())->method('getSockId')->willReturn(null);
        $sockAccountService = $this->getMockBuilder(AccountService::class)->disableOriginalConstructor()->getMock();
        $service->createSockAccount($appEnvironment, $server, $sockAccountService);
    }

    public function testCreateSockAccountExists()
    {
        $service = $this->getService();

        $user = $this->getAlphaNumeric();

        $serverSettings = $this->getMockBuilder(ServerSettings::class)->disableOriginalConstructor()->getMock();
        $serverSettings->expects($this->any())->method('getUser')->willReturn($user);

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnvironment->expects($this->any())->method('getServerSettings')->willReturn($serverSettings);

        $sockId = uniqid();

        $server = $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock();
        $server->expects($this->any())->method('getSockId')->willReturn($sockId);

        $account = $this->getMockBuilder(Account::class)->getMock();
        $account->expects($this->any())->method('getId')->willReturn($sockId);

        $sockAccountService = $this->getMockBuilder(AccountService::class)->disableOriginalConstructor()->getMock();
        $sockAccountService->expects($this->once())->method('findByName')->with($user, $sockId)->willReturn($account);

        $serverSettings->expects($this->once())->method('setSockAccountId')->with($sockId);

        $promise = $service->createSockAccount($appEnvironment, $server, $sockAccountService);

        $this->assertInstanceOf(EntityCreatePromise::class, $promise);
        $this->assertTrue($promise->getIsResolved());
        $this->assertTrue($promise->getIsCreated());
        $this->assertTrue($promise->getDidExist());
        $this->assertEquals($account, $promise->getEntity());
    }

    public function testCreateSockAccountNew()
    {
        $service = $this->getService();

        $user = $this->getAlphaNumeric();

        $serverSettings = $this->getMockBuilder(ServerSettings::class)->disableOriginalConstructor()->getMock();
        $serverSettings->expects($this->any())->method('getUser')->willReturn($user);

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnvironment->expects($this->any())->method('getServerSettings')->willReturn($serverSettings);

        $sockId = uniqid();

        $server = $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock();
        $server->expects($this->any())->method('getSockId')->willReturn($sockId);

        $sockAccountService = $this->getMockBuilder(AccountService::class)->disableOriginalConstructor()->getMock();
        $sockAccountService->expects($this->once())->method('findByName')->with($user, $sockId)->willReturn(null);

        $sshKeys = [$this->getAlphaNumeric(), $this->getAlphaNumeric()];

        $this->settings->expects($this->atLeastOnce())->method('getDefaultSockSshKeys')->willReturn(implode(',', $sshKeys));

        $accountId = uniqid();

        $sockAccountService->expects($this->once())->method('create')->with($this->callback(function (Account $account) use ($sockId, $user, $sshKeys) {
            return $sockId === $account->getServerId() && $user === $account->getName() && $sshKeys === $account->getSshKeys();
        }
        ))->willReturnCallback(function (Account $account) use ($accountId) {
            $account->setId($accountId);

            return $account;
        }
        );

        $serverSettings->expects($this->once())->method('setSockAccountId')->with($accountId);

        $promise = $service->createSockAccount($appEnvironment, $server, $sockAccountService);

        $this->assertInstanceOf(EntityCreatePromise::class, $promise);
        $this->assertFalse($promise->getIsResolved());
        $this->assertFalse($promise->getIsCreated());
        $this->assertFalse($promise->getDidExist());
        $this->assertEquals($sockId, $promise->getEntity()->getServerId());
        $this->assertEquals($user, $promise->getEntity()->getName());
        $this->assertEquals($sshKeys, $promise->getEntity()->getSshKeys());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateSockApplicationNoSockId()
    {
        $service = $this->getService();

        $serverSettings = $this->getMockBuilder(ServerSettings::class)->disableOriginalConstructor()->getMock();
        $serverSettings->expects($this->any())->method('getSockAccountId')->willReturn(null);

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnvironment->expects($this->any())->method('getServerSettings')->willReturn($serverSettings);

        $appService = $this->getMockBuilder(ApplicationService::class)->disableOriginalConstructor()->getMock();

        $service->createSockApplication($appEnvironment, $appService);
    }

    public function testCreateSockApplicationExists()
    {
        $service = $this->getService();

        $sockId = uniqid();

        $serverSettings = $this->getMockBuilder(ServerSettings::class)->disableOriginalConstructor()->getMock();
        $serverSettings->expects($this->any())->method('getSockAccountId')->willReturn($sockId);

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getParent')->willReturn(null);

        $sockAppId = uniqid();

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnvironment->expects($this->any())->method('getApplication')->willReturn($app);
        $appEnvironment->expects($this->once())->method('setSockApplicationId')->willReturn($sockAppId);
        $appEnvironment->expects($this->any())->method('getServerSettings')->willReturn($serverSettings);

        $sockApp = $this->getMockBuilder(SockApplication::class)->disableOriginalConstructor()->getMock();
        $sockApp->expects($this->once())->method('getId')->willReturn($sockAppId);

        $sockApplicationService = $this->getMockBuilder(ApplicationService::class)->disableOriginalConstructor()->getMock();
        $sockApplicationService->expects($this->once())->method('findByName')->with($sockId, 'default')->willReturn($sockApp);

        $promise = $service->createSockApplication($appEnvironment, $sockApplicationService);

        $this->assertInstanceOf(EntityCreatePromise::class, $promise);
        $this->assertTrue($promise->getIsResolved());
        $this->assertTrue($promise->getIsCreated());
        $this->assertTrue($promise->getDidExist());
        $this->assertEquals($sockApp, $promise->getEntity());
    }

    public function testCreateSockApplicationNew()
    {
        $service = $this->getService();

        $sockId = uniqid();

        $serverSettings = $this->getMockBuilder(ServerSettings::class)->disableOriginalConstructor()->getMock();
        $serverSettings->expects($this->any())->method('getSockAccountId')->willReturn($sockId);

        $name = $this->getAlphaNumeric(false, 14);
        $slug = $this->getAlphaNumeric();

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getParent')->willReturn('parent');
        $app->expects($this->once())->method('getNameCanonical')->willReturn($name);
        $app->expects($this->once())->method('getAppTypeSlug')->willReturn($slug);

        $sockAppId = uniqid();
        $domains = [$this->getAlphaNumeric(), $this->getAlphaNumeric()];

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnvironment->expects($this->any())->method('getApplication')->willReturn($app);
        $appEnvironment->expects($this->once())->method('setSockApplicationId')->willReturn($sockAppId);
        $appEnvironment->expects($this->any())->method('getServerSettings')->willReturn($serverSettings);
        $appEnvironment->expects($this->once())->method('getDomains')->willReturn($domains);

        $appType = $this->getMockBuilder(ApplicationTypeInterface::class)->getMock();
        $appType->expects($this->once())->method('getPublicFolder')->willReturn('web');

        $this->applicationTypeBuilder->expects($this->once())->method('getType')->with($slug)->willReturn($appType);

        $appId = uniqid();

        $sockApplicationService = $this->getMockBuilder(ApplicationService::class)->disableOriginalConstructor()->getMock();
        $sockApplicationService->expects($this->once())->method('findByName')->with($sockId, $name)->willReturn(false);
        $sockApplicationService->expects($this->once())->method('create')->with($this->callback(function (SockApplication $sockApp) use ($sockId, $name, $domains) {
            return $sockApp->getAccountId() === $sockId && $sockApp->getName() === $name && $sockApp->getAliases() === $domains && 'current/web' === $sockApp->getDocumentRoot();
        }))
            ->willReturnCallback(function (SockApplication $sockApp) use ($appId) {
                $sockApp->setId($appId);

                return $sockApp;
            }
        );

        $promise = $service->createSockApplication($appEnvironment, $sockApplicationService);

        $this->assertInstanceOf(EntityCreatePromise::class, $promise);
        $this->assertFalse($promise->getIsResolved());
        $this->assertFalse($promise->getIsCreated());
        $this->assertFalse($promise->getDidExist());
        $this->assertEquals($sockId, $promise->getEntity()->getAccountId());
        $this->assertEquals($name, $promise->getEntity()->getName());
        $this->assertEquals($domains, $promise->getEntity()->getAliases());
        $this->assertEquals('current/web', $promise->getEntity()->getDocumentRoot());
        $this->assertEquals($appId, $promise->getEntity()->getId());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateSockDatabaseNoSockId()
    {
        $service = $this->getService();

        $serverSettings = $this->getMockBuilder(ServerSettings::class)->disableOriginalConstructor()->getMock();
        $serverSettings->expects($this->any())->method('getSockAccountId')->willReturn(null);

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnvironment->expects($this->any())->method('getServerSettings')->willReturn($serverSettings);

        $dbService = $this->getMockBuilder(DatabaseService::class)->disableOriginalConstructor()->getMock();

        $service->createSockDatabase($appEnvironment, $dbService);
    }

    public function testCreateSockDatabaseExists()
    {
        $service = $this->getService();

        $sockId = uniqid();

        $serverSettings = $this->getMockBuilder(ServerSettings::class)->disableOriginalConstructor()->getMock();
        $serverSettings->expects($this->any())->method('getSockAccountId')->willReturn($sockId);

        $dbName = $this->getAlphaNumeric();

        $dbSettings = $this->getMockBuilder(DatabaseSettings::class)->disableOriginalConstructor()->getMock();
        $dbSettings->expects($this->once())->method('getName')->willReturn($dbName);

        $sockDbId = uniqid();

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnvironment->expects($this->any())->method('getServerSettings')->willReturn($serverSettings);
        $appEnvironment->expects($this->any())->method('getDatabaseSettings')->willReturn($dbSettings);

        $sockDb = $this->getMockBuilder(Database::class)->disableOriginalConstructor()->getMock();
        $sockDb->expects($this->once())->method('getId')->willReturn($sockDbId);

        $sockDbService = $this->getMockBuilder(DatabaseService::class)->disableOriginalConstructor()->getMock();
        $sockDbService->expects($this->once())->method('findByName')->with($sockId, $dbName)->willReturn($sockDb);

        $promise = $service->createSockDatabase($appEnvironment, $sockDbService);

        $this->assertInstanceOf(EntityCreatePromise::class, $promise);
        $this->assertTrue($promise->getIsResolved());
        $this->assertTrue($promise->getIsCreated());
        $this->assertTrue($promise->getDidExist());
        $this->assertEquals($sockDb, $promise->getEntity());
    }

    public function testCreateSockDatabaseNew()
    {
        $service = $this->getService();

        $sockId = uniqid();

        $serverSettings = $this->getMockBuilder(ServerSettings::class)->disableOriginalConstructor()->getMock();
        $serverSettings->expects($this->any())->method('getSockAccountId')->willReturn($sockId);

        $dbName = $this->getAlphaNumeric();
        $dbUser = $this->getAlphaNumeric();
        $dbPassword = $this->getAlphaNumeric();
        $dbEngine = $this->getAlphaNumeric();

        $dbSettings = $this->getMockBuilder(DatabaseSettings::class)->disableOriginalConstructor()->getMock();
        $dbSettings->expects($this->any())->method('getName')->willReturn($dbName);
        $dbSettings->expects($this->any())->method('getUser')->willReturn($dbUser);
        $dbSettings->expects($this->any())->method('getPassword')->willReturn($dbPassword);
        $dbSettings->expects($this->any())->method('getEngine')->willReturn($dbEngine);

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnvironment->expects($this->any())->method('getServerSettings')->willReturn($serverSettings);
        $appEnvironment->expects($this->any())->method('getDatabaseSettings')->willReturn($dbSettings);

        $sockDbId = uniqid();

        $sockDbService = $this->getMockBuilder(DatabaseService::class)->disableOriginalConstructor()->getMock();
        $sockDbService->expects($this->once())->method('findByName')->with($sockId, $dbName)->willReturn(false);
        $sockDbService->expects($this->once())->method('create')->with($this->callback(function (Database $db) use ($sockId, $dbName, $dbUser, $dbPassword, $dbEngine) {
            return $db->getAccountId() === $sockId && $db->getEngine() === $dbEngine && $db->getLogin() === $dbUser && $db->getName() === $dbName && $db->getPassword() === $dbPassword;
        }))->willReturnCallback(function (Database $db) use ($sockDbId) {
            $db->setId($sockDbId);

            return $db;
        });

        $promise = $service->createSockDatabase($appEnvironment, $sockDbService);

        $this->assertInstanceOf(EntityCreatePromise::class, $promise);
        $this->assertFalse($promise->getIsResolved());
        $this->assertFalse($promise->getIsCreated());
        $this->assertFalse($promise->getDidExist());
        $this->assertEquals($sockId, $promise->getEntity()->getAccountId());
        $this->assertEquals($dbUser, $promise->getEntity()->getLogin());
        $this->assertEquals($dbEngine, $promise->getEntity()->getEngine());
        $this->assertEquals($dbName, $promise->getEntity()->getName());
        $this->assertEquals($dbPassword, $promise->getEntity()->getPassword());
        $this->assertEquals($sockDbId, $promise->getEntity()->getId());
    }

    public function testCreateServerFilesystem()
    {
        $service = $this->getService();

        $task = $this->getMockBuilder(TaskInterface::class)->getMock();

        $result = $this->getMockBuilder(TaskResult::class)->getMock();
        $result->expects($this->once())->method('isSuccess')->willReturn(true);

        $runner = $this->getMockBuilder(TaskRunnerInterface::class)->getMock();
        $runner->expects($this->once())->method('addTask')->with($task);
        $runner->expects($this->once())->method('run')->willReturn($result);

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $servers = [
            $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock(),
        ];

        $this->taskFactory->setRunner($runner);
        $this->taskFactory->setTask($task);
        $this->taskFactory->setExpectedArguments(
            [
                'provision.filesystem',
                [
                    'appEnvironment' => $appEnvironment,
                    'settings' => $this->settings,
                    'servers' => $servers,
                    'applicationTypeBuilder' => $this->applicationTypeBuilder,
                ],
            ]
        );

        $service->createServerFilesystem($appEnvironment, $servers);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage failed to create server filesystem
     */
    public function testCreateServerFilesystemFailed()
    {
        $service = $this->getService();

        $task = $this->getMockBuilder(TaskInterface::class)->getMock();

        $result = $this->getMockBuilder(TaskResult::class)->getMock();
        $result->expects($this->once())->method('isSuccess')->willReturn(false);

        $runner = $this->getMockBuilder(TaskRunnerInterface::class)->getMock();
        $runner->expects($this->once())->method('addTask')->with($task);
        $runner->expects($this->once())->method('run')->willReturn($result);

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $servers = [
            $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock(),
        ];

        $this->taskFactory->setRunner($runner);
        $this->taskFactory->setTask($task);
        $this->taskFactory->setExpectedArguments(
            [
                'provision.filesystem',
                [
                    'appEnvironment' => $appEnvironment,
                    'settings' => $this->settings,
                    'servers' => $servers,
                    'applicationTypeBuilder' => $this->applicationTypeBuilder,
                ],
            ]
        );

        $service->createServerFilesystem($appEnvironment, $servers);
    }

    public function testCreateServerConfigFiles()
    {
        $service = $this->getService();

        $task = $this->getMockBuilder(TaskInterface::class)->getMock();

        $result = $this->getMockBuilder(TaskResult::class)->getMock();
        $result->expects($this->once())->method('isSuccess')->willReturn(true);

        $runner = $this->getMockBuilder(TaskRunnerInterface::class)->getMock();
        $runner->expects($this->once())->method('addTask')->with($task);
        $runner->expects($this->once())->method('run')->willReturn($result);

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $servers = [
            $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock(),
        ];

        $this->taskFactory->setRunner($runner);
        $this->taskFactory->setTask($task);
        $this->taskFactory->setExpectedArguments(
            [
                'provision.config_files',
                [
                    'appEnvironment' => $appEnvironment,
                    'settings' => $this->settings,
                    'servers' => $servers,
                    'applicationTypeBuilder' => $this->applicationTypeBuilder,
                ],
            ]
        );

        $service->createServerConfigFiles($appEnvironment, $servers);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage failed to create server config files
     */
    public function testCreateServerConfigFilesFailed()
    {
        $service = $this->getService();

        $task = $this->getMockBuilder(TaskInterface::class)->getMock();

        $result = $this->getMockBuilder(TaskResult::class)->getMock();
        $result->expects($this->once())->method('isSuccess')->willReturn(false);

        $runner = $this->getMockBuilder(TaskRunnerInterface::class)->getMock();
        $runner->expects($this->once())->method('addTask')->with($task);
        $runner->expects($this->once())->method('run')->willReturn($result);

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $servers = [
            $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock(),
        ];

        $this->taskFactory->setRunner($runner);
        $this->taskFactory->setTask($task);
        $this->taskFactory->setExpectedArguments(
            [
                'provision.config_files',
                [
                    'appEnvironment' => $appEnvironment,
                    'settings' => $this->settings,
                    'servers' => $servers,
                    'applicationTypeBuilder' => $this->applicationTypeBuilder,
                ],
            ]
        );

        $service->createServerConfigFiles($appEnvironment, $servers);
    }

    public function testCreateCronJob()
    {
        $service = $this->getService();

        $task = $this->getMockBuilder(TaskInterface::class)->getMock();

        $result = $this->getMockBuilder(TaskResult::class)->getMock();
        $result->expects($this->once())->method('isSuccess')->willReturn(true);

        $runner = $this->getMockBuilder(TaskRunnerInterface::class)->getMock();
        $runner->expects($this->once())->method('addTask')->with($task);
        $runner->expects($this->once())->method('run')->willReturn($result);

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getCron')->willReturn($this->getAlphaNumeric());

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnvironment->expects($this->once())->method('getApplication')->willReturn($app);
        $servers = [
            $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock(),
        ];

        $this->taskFactory->setRunner($runner);
        $this->taskFactory->setTask($task);
        $this->taskFactory->setExpectedArguments(
            [
                'provision.cron',
                [
                    'appEnvironment' => $appEnvironment,
                    'servers' => $servers,
                ],
            ]
        );

        $service->createCronJob($appEnvironment, $servers);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage failed to install cron job
     */
    public function testCreateCronJobFailed()
    {
        $service = $this->getService();

        $task = $this->getMockBuilder(TaskInterface::class)->getMock();

        $result = $this->getMockBuilder(TaskResult::class)->getMock();
        $result->expects($this->once())->method('isSuccess')->willReturn(false);

        $runner = $this->getMockBuilder(TaskRunnerInterface::class)->getMock();
        $runner->expects($this->once())->method('addTask')->with($task);
        $runner->expects($this->once())->method('run')->willReturn($result);

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getCron')->willReturn($this->getAlphaNumeric());

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnvironment->expects($this->once())->method('getApplication')->willReturn($app);
        $servers = [
            $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock(),
        ];

        $this->taskFactory->setRunner($runner);
        $this->taskFactory->setTask($task);
        $this->taskFactory->setExpectedArguments(
            [
                'provision.cron',
                [
                    'appEnvironment' => $appEnvironment,
                    'servers' => $servers,
                ],
            ]
        );

        $service->createCronJob($appEnvironment, $servers);
    }

    public function testCreateCronJobNoCron()
    {
        $service = $this->getService();

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getCron')->willReturn(false);

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnvironment->expects($this->once())->method('getApplication')->willReturn($app);
        $servers = [
            $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock(),
        ];

        $this->assertFalse($service->createCronJob($appEnvironment, $servers));
    }

    /**
     * @return AppEnvironmentService
     */
    protected function getService()
    {
        return new AppEnvironmentService($this->settings, $this->applicationTypeBuilder, $this->taskFactory);
    }
}
