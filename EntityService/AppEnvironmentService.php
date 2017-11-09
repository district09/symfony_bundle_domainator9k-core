<?php

namespace DigipolisGent\Domainator9k\CoreBundle\EntityService;

use Ctrl\Common\EntityService\AbstractDoctrineService;
use DigipolisGent\Domainator9k\CiTypes\JenkinsBundle\Service\Jenkins;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Task\FactoryInterface;
use DigipolisGent\SockAPIBundle\JsonModel\Account;
use DigipolisGent\SockAPIBundle\JsonModel\Application as SockApp;
use DigipolisGent\SockAPIBundle\JsonModel\Database;
use DigipolisGent\SockAPIBundle\Service\AccountService;
use DigipolisGent\SockAPIBundle\Service\ApplicationService as SockAppService;
use DigipolisGent\SockAPIBundle\Service\DatabaseService;
use DigipolisGent\SockAPIBundle\Service\Event\Poller;
use DigipolisGent\SockAPIBundle\Service\Promise\EntityCreatePromise;
use Exception;
use InvalidArgumentException;

// @codeCoverageIgnoreStart
define('SOCK_MAX_SECONDS', '900');

// @codeCoverageIgnoreEnd

class AppEnvironmentService extends AbstractDoctrineService
{

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var Jenkins
     */
    protected $jenkins;

    /**
     * @var ApplicationTypeBuilder
     */
    protected $applicationTypeBuilder;

    /**
     * @var FactoryInterface
     */
    protected $taskFactory;

    /**
     * @param Settings $settings
     * @param ApplicationTypeBuilder $appTypeBuilder
     * @param FactoryInterface $taskFactory
     */
    public function __construct(
        Settings $settings,
        ApplicationTypeBuilder $appTypeBuilder,
        FactoryInterface $taskFactory
    ) {
        $this->settings = $settings;
        $this->applicationTypeBuilder = $appTypeBuilder;
        $this->taskFactory = $taskFactory;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return AppEnvironment::class;
    }
    // SOCK

    /**
     * Creates an account on the server for the environment if one could not be found
     * based on the application's name.
     *
     * @param AppEnvironment $appEnvironment
     * @param Server         $server
     * @param AccountService $sockAccountService
     *
     * @return EntityCreatePromise
     *
     * @throws InvalidArgumentException
     * @throws Exception
     *
     * @todo Shouldn't this be in a separate sock bundle??
     */
    public function createSockAccount(AppEnvironment $appEnvironment, Server $server, AccountService $sockAccountService)
    {
        if (!$server->getSockId()) {
            throw new InvalidArgumentException(sprintf(
                "Can not create account on sock: Environment '%s' has no server assigned",
                $appEnvironment->getName()
            ));
        }

        // check if an account is present for this environment
        $account = $sockAccountService->findByName(
            $appEnvironment->getServerSettings()->getUser(),
            $server->getSockId()
        );

        if ($account) {
            $promise = new EntityCreatePromise($account);
            $promise
                ->setResolved(true)
                ->setIsCreated(true)
                ->setDidExist(true);

            $appEnvironment->getServerSettings()->setSockAccountId($account->getId());
            return $promise;
        }
        $account = new Account();
        $account
            ->setServerId($server->getSockId())
            ->setName($appEnvironment->getServerSettings()->getUser());

        if ($this->settings->getDefaultSockSshKeys()) {
            $keys = explode(',', $this->settings->getDefaultSockSshKeys());
            $account->setSshKeys($keys);
        }

        /** @var Account $account */
        $account = $sockAccountService->create($account);

        $promise = new EntityCreatePromise($account);
        $promise
            ->setEntity($account)
            ->setPoller(new Poller($sockAccountService, $account->getId(), 'account create'));

        $appEnvironment->getServerSettings()->setSockAccountId($account->getId());

        return $promise;
    }

    /**
     * Creates an application on the server for the environment if one could not be found.
     *
     * @param AppEnvironment $appEnvironment
     * @param SockAppService $sockAppService
     *
     * @return EntityCreatePromise
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function createSockApplication(AppEnvironment $appEnvironment, SockAppService $sockAppService)
    {
        if (!$appEnvironment->getServerSettings()->getSockAccountId()) {
            throw new InvalidArgumentException(sprintf(
                "Can not create application on sock: Environment '%s' has no account assigned",
                $appEnvironment->getName()
            ));
        }

        $appName = 'default';
        if (null !== $appEnvironment->getApplication()->getParent()) {
            $appName = substr($appEnvironment->getApplication()->getNameCanonical(), 0, 14);
        }

        // check if an application is present for this environment
        $app = $sockAppService->findByName(
            $appEnvironment->getServerSettings()->getSockAccountId(),
            $appName
        );

        if ($app) {
            $promise = new EntityCreatePromise($app);
            $promise
                ->setResolved(true)
                ->setIsCreated(true)
                ->setDidExist(true);

            $appEnvironment->setSockApplicationId($app->getId());
            return $promise;
        }
        $appType = $this->applicationTypeBuilder->getType($appEnvironment->getApplication()->getAppTypeSlug());

        $app = new SockApp();
        $app
            ->setAccountId($appEnvironment->getServerSettings()->getSockAccountId())
            ->setName($appName)
            ->setAliases($appEnvironment->getDomains())
            ->setDocumentRoot(
                'current/' . $appType->getPublicFolder()
            );

        /** @var SockApp $app */
        $app = $sockAppService->create($app);

        $promise = new EntityCreatePromise($app);
        $promise
            ->setEntity($app)
            ->setPoller(new Poller($sockAppService, $app->getId(), 'application create'));
        $appEnvironment->setSockApplicationId($app->getId());

        return $promise;
    }

    /**
     * Creates a database on the server for the environment if one could not be found.
     *
     * @param AppEnvironment  $appEnvironment
     * @param DatabaseService $sockDatabaseService
     *
     * @return EntityCreatePromise
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function createSockDatabase(AppEnvironment $appEnvironment, DatabaseService $sockDatabaseService)
    {
        $accountId = $appEnvironment->getServerSettings()->getSockAccountId();

        if (!$appEnvironment->getServerSettings()->getSockAccountId()) {
            throw new InvalidArgumentException(sprintf(
                "Can not create database on sock: Environment '%s' has no account assigned",
                $appEnvironment->getName()
            ));
        }

        $dbSettings = $appEnvironment->getDatabaseSettings();

        // check if a database is already present for this environment
        $db = $sockDatabaseService->findByName(
            $accountId,
            $dbSettings->getName()
        );

        if ($db) {
            $promise = new EntityCreatePromise($db);
            $promise
                ->setResolved(true)
                ->setIsCreated(true)
                ->setDidExist(true);
            $dbSettings->setSockDatabaseId($db->getId());

            return $promise;
        }
        $db = new Database();
        $db
            ->setAccountId($accountId)
            ->setName($dbSettings->getName())
            ->setLogin($dbSettings->getUser())
            ->setPassword($dbSettings->getPassword())
            ->setEngine($dbSettings->getEngine());

        /** @var Database $db */
        $db = $sockDatabaseService->create($db);

        $promise = new EntityCreatePromise($db);
        $promise
            ->setEntity($db)
            ->setPoller(new Poller($sockDatabaseService, $db->getId(), 'database create'));
        $dbSettings->setSockDatabaseId($db->getId());

        return $promise;
    }
    // FILES AND DIRECTORIES

    /**
     * @param AppEnvironment $appEnvironment
     * @param array|Server[] $servers
     *
     * @throws Exception
     */
    public function createServerFilesystem(AppEnvironment $appEnvironment, array $servers)
    {
        $taskRunner = $this->taskFactory->createRunner();

        $taskRunner->addTask(
            $this->taskFactory->create(
                'provision.filesystem',
                [
                    'appEnvironment' => $appEnvironment,
                    'settings' => $this->settings,
                    'servers' => $servers,
                    'applicationTypeBuilder' => $this->applicationTypeBuilder,
                ]
            )
        );

        $result = $taskRunner->run();

        if (!$result->isSuccess()) {
            throw new Exception('failed to create server filesystem');
        }
    }

    /**
     * @param AppEnvironment $appEnvironment
     * @param array|Server[] $servers
     *
     * @throws Exception
     */
    public function createServerConfigFiles(AppEnvironment $appEnvironment, array $servers)
    {
        $taskRunner = $this->taskFactory->createRunner();

        $taskRunner->addTask(
            $this->taskFactory->create(
                'provision.config_files',
                [
                    'appEnvironment' => $appEnvironment,
                    'servers' => $servers,
                    'settings' => $this->settings,
                    'applicationTypeBuilder' => $this->applicationTypeBuilder,
                ]
            )
        );

        $result = $taskRunner->run();

        if (!$result->isSuccess()) {
            throw new Exception('failed to create server config files');
        }
    }

    /**
     * @param AppEnvironment $appEnvironment
     * @param array          $servers
     *
     * @throws Exception
     *
     * @return bool
     */
    public function createCronJob(AppEnvironment $appEnvironment, array $servers)
    {
        $cron = trim($appEnvironment->getApplication()->getCron());
        if (!$cron || !count($servers)) {
            return false;
        }

        $taskRunner = $this->taskFactory->createRunner();

        $taskRunner->addTask(
            $this->taskFactory->create(
                'provision.cron',
                [
                    'appEnvironment' => $appEnvironment,
                    'servers' => $servers,
                ]
            )
        );

        $result = $taskRunner->run();

        if (!$result->isSuccess()) {
            throw new Exception('failed to install cron job');
        }

        return true;
    }
}
