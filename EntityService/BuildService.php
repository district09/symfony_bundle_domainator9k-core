<?php

namespace DigipolisGent\Domainator9k\CoreBundle\EntityService;

use Ctrl\Common\EntityService\AbstractDoctrineService;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Interfaces\CiProcessorInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\Messenger;
use DigipolisGent\SockAPIBundle\Service\AccountService;
use DigipolisGent\SockAPIBundle\Service\DatabaseService;
use DigipolisGent\SockAPIBundle\Service\Event\Poller;
use DigipolisGent\SockAPIBundle\Service\Promise\EntityCreatePromise;
use DigipolisGent\SockAPIBundle\Service\Promise\PromiseQueue;
use Exception;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class BuildService extends AbstractDoctrineService implements ContainerAwareInterface
{
    const PROVISION_ALL = 63;
    const PROVISION_CI = 1;
    const PROVISION_CI_OVERRIDE = 2;
    const PROVISION_FILESYSTEM = 4;
    const PROVISION_CONFIG_FILES = 8;
    const PROVISION_SOCK = 16;
    const PROVISION_CRON = 32;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $workspaceDirectory;

    /**
     * @var string
     */
    protected $kernelDir;

    /**
     * @var processBuilder
     */
    protected $processBuilder;

    /**
     * Creates a new build service
     * @param string $workspaceDirectory
     * @param string $kernelDir
     * @param null|ProcessBuilder $processBuilder
     */
    public function __construct($workspaceDirectory, $kernelDir, ProcessBuilder $processBuilder = null)
    {
        $this->workspaceDirectory = $workspaceDirectory;
        $this->kernelDir = $kernelDir;
        if (is_null($processBuilder)) {
            $processBuilder = new ProcessBuilder();
        }
        $this->processBuilder = $processBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return Build::class;
    }

    /**
     * Gets the container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface $container
     *
     * @return $this
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Gets the workspace directory.
     *
     * @return string
     */
    public function getWorkspaceDirectory()
    {
        return $this->workspaceDirectory;
    }

    /**
     * Sets the workspace directory.
     *
     * @param string $workspaceDirectory
     *
     * @return $this
     */
    public function setWorkspaceDirectory($workspaceDirectory)
    {
        $this->workspaceDirectory = $workspaceDirectory;

        return $this;
    }

    /**
     * Gets the kernel directory.
     *
     * @return string
     */
    public function getKernelDir()
    {
        return $this->kernelDir;
    }

    /**
     * Updates the build log.
     *
     * @param Build $build
     * @param string $newMessage
     * @param bool $persist
     */
    public function updateBuildLog(Build $build, $newMessage, $persist = true)
    {
        $timestamp = str_pad(date('Y-m-d H:i:s'), 25);
        $newMessage = str_replace(PHP_EOL, PHP_EOL . str_repeat(' ', 25), $newMessage);
        $build->setLog(
            $build->getLog() . PHP_EOL . $timestamp . $newMessage
        );

        if ($persist) {
            $this->persist($build);
        }
    }

    /**
     * @param Build $build
     * @param array|Server[] $servers
     * @param Settings $settings
     * @param int $provision
     *
     * @throws RuntimeException
     *
     * @return bool
     *
     * @todo Very hard to test right now. This will switch to an event system
     * which will make it easier to test.
     *
     * @codeCoverageIgnore
     */
    public function execute(Build $build, array $servers, Settings $settings, $provision = self::PROVISION_ALL)
    {
        try {
            $service = $this;
            Messenger::addListener(function ($message) use ($build, $service) {
                $service->updateBuildLog($build, $message);
            }, 'db_update_build_log');

            switch ($build->getType()) {
                case Build::TYPE_PROVISION:
                    $this->executeProvision($build, $servers, $settings, $provision);

                    break;
            }

            return true;
        } catch (Exception $e) {
            Messenger::send([
                'ERROR build failed',
                sprintf('%s says: "%s"', get_class($e), $e->getMessage()),
                $e->getTraceAsString(),
            ]);
        }

        $build->setSuccess(false);
        $build->setCompleted(true);
        $this->persist($build);

        return false;
    }

    /**
     * Executes the provision.
     *
     * @param Build $build
     * @param array|Server[] $servers
     * @param Settings $settings
     * @param int $options
     *
     * @throws Exception
     *
     * @return bool
     *
     * @todo Very hard to test right now. This will switch to an event system
     * which will make it easier to test.
     *
     * @codeCoverageIgnore
     */
    protected function executeProvision(Build $build, array $servers, Settings $settings, $options = self::PROVISION_ALL)
    {
        $build->setStarted(true);
        $app = $build->getApplication();
        $allowPartialBuilds = !$app->allowPartialBuilds();
        $allActive = $this->isEnabled($options, self::PROVISION_ALL);
        $ciOverrideActive = $this->isEnabled($options, self::PROVISION_CI_OVERRIDE);
        $ciActive = $ciOverrideActive || $this->isEnabled($options, self::PROVISION_CI);
        $envService = $this->container->get('digip_deploy.entity.appenvironment');
        $appTypeBuilder = $this->container->get('digip_deploy.application_type_builder');
        $appType = $appTypeBuilder->getType($app->getAppTypeSlug());
        $ciTypeBuilder = $this->container->get('digip_deploy.ci_type_builder');
        $ciType = $ciTypeBuilder->getType($app->getCiTypeSlug());
        /** @var CiProcessorInterface $ciProcessor */
        $ciProcessor = $this->container->get($ciType->getProcessorServiceClass());

        $ciAppTypeSettings = $this->container->get('digip_deploy.ci_apptype_settings_service')->getSettings($ciType, $appType);

        Messenger::send(sprintf(
                'starting new provision build for %s
ci:            %s
ciOverride:    %s
filesystem:         %s
config files:       %s
sock:               %s
cron:               %s', $build->getApplication()->getName(), ($ciActive) ? 'On' : 'Off', ($this->isEnabled($options, self::PROVISION_CI_OVERRIDE)) ? 'On' : 'Off', ($allActive || $this->isEnabled($options, self::PROVISION_FILESYSTEM)) ? 'On' : 'Off', ($allActive || $this->isEnabled($options, self::PROVISION_CONFIG_FILES)) ? 'On' : 'Off', ($allActive || $this->isEnabled($options, self::PROVISION_SOCK)) ? 'On' : 'Off', (($allActive || $this->isEnabled($options, self::PROVISION_CRON)) && $allowPartialBuilds) ? 'On' : 'Off'
        ));

        if (!$allActive && !$allowPartialBuilds && 'dev' !== $this->container->getParameter('kernel.environment')) {
            Messenger::send('a partial build was requested, but is seems we are missing required parts');

            throw new RuntimeException('Partial build not possible');
        }

        Messenger::send($this->getCliHeader('APPLICATION WIDE TASKS'));

        //TODO ciapptypesettings interface ?
        if ($ciActive && $ciAppTypeSettings->isValidateJobEnabled()) {
            $ciProcessor->createValidateJob($app, $ciOverrideActive);
            //$envService->createJenkinsValidateJob($app, $ciOverrideActive);
        }

        foreach ($app->getAppEnvironments() as $env) {
            Messenger::send($this->getCliHeader(sprintf('ENVIRONMENT %s', $env->getName())));

            /** @var Server[] $envServers */
            $envServers = array();
            foreach ($servers as $server) {
                if ($server->getEnvironment() !== $env->getNameCanonical()) {
                    continue;
                }
                $envServers[] = $server;
                if (!$server->manageSock()) {
                    continue;
                }

                if ($this->isEnabled($options, self::PROVISION_SOCK)) {
                    Messenger::send($this->getCliHeader(sprintf('SOCK CONFIG FOR SERVER %s', $server->getName()), '-'));
                    $this->executeSockProvision($env, $server);
                }
            }

            $envServersCount = count($envServers);

            if (!$envServersCount) {
                Messenger::send('no servers configured for this environment.');

                continue;
            }

            if ($this->isEnabled($options, self::PROVISION_FILESYSTEM)) {
                Messenger::send(sprintf('creating filesystem on %s servers', $envServersCount));
                if ($envServersCount) {
                    $envService->createServerFilesystem($env, $envServers);
                }
            }

            if ($this->isEnabled($options, self::PROVISION_CONFIG_FILES)) {
                Messenger::send(sprintf('creating config files on %s servers', $envServersCount));
                if ($envServersCount) {
                    $envService->createServerConfigFiles($env, $envServers);
                }
            }

            if (!$allowPartialBuilds && $this->isEnabled($options, self::PROVISION_CRON)) {
                $cronJobResult = $envService->createCronJob($env, $envServers);
                Messenger::send($cronJobResult ? 'cron jobs installed' : 'no cron jobs installed');
            }

            if ($ciActive) {
                Messenger::send(sprintf('creating ci jobs for %s (%s servers)', $env->getNameCanonical(), $envServersCount));
                $ciProcessor->createDeployJob($env, $envServers, $ciOverrideActive);

                if ($ciAppTypeSettings->isRevertJobEnabled()) {
                    $ciProcessor->createRevertJob($env, $envServers, $ciOverrideActive);
                }
                if ($ciAppTypeSettings->isSyncJobEnabled()) {
                    if (!$env->isProd()) {
                        $ciProcessor->createSyncJob($app->getProdAppEnvironment(), $env, $servers, $ciOverrideActive);
                    } else {
                        if ($ciAppTypeSettings->isDumpJobEnabled()) {
                            $ciProcessor->createDumpJob(
                                $app->getProdAppEnvironment(), $envServers, $ciOverrideActive
                            );
                        }
                    }
                }
            }

            $envService->persist($env);
        }

        Messenger::send($this->getCliHeader('EXTRA TASKS'));

        if ($app->isDnsMailSent()) {
            Messenger::send(sprintf('DNS mail already sent, not sending it again'));
        } else {
            Messenger::send(sprintf('sending DNS mail to %s', $settings->getDnsMailRecipients()));
            $this->container->get('digip_deploy.mailer')->sendDnsMail(
                $settings, $app, $servers
            );
        }

        $build->setCompleted(true);
        $build->setSuccess(true);
        $this->persist($build);

        return true;
    }

    /**
     * Helper function to get a cli header.
     *
     * @param string $text
     * @param string $padChar
     *
     * @return string
     *
     * @todo Very hard to test right now. This will switch to an event system
     * which will make it easier to test.
     *
     * @codeCoverageIgnore
     */
    protected function getCliHeader($text, $padChar = '=')
    {
        return PHP_EOL . str_pad(str_repeat($padChar, 20) . ' ' . trim($text) . ' ', 80, $padChar);
    }

    /**
     * Executes the sock provision.
     *
     * @param AppEnvironment $env
     * @param Server $server
     *
     * @throws Exception
     *
     * @todo Very hard to test right now. This will switch to an event system
     * which will make it easier to test.
     *
     * @codeCoverageIgnore
     */
    protected function executeSockProvision(AppEnvironment $env, Server $server)
    {
        $app = $env->getApplication();

        /** @var AppEnvironmentService $envService */
        $envService = $this->container->get('digip_deploy.entity.appenvironment');
        /** @var AccountService $sockAccountService */
        $sockAccountService = $this->container->get('digip_deploy.sock_api.account');
        /** @var \DigipolisGent\SockAPIBundle\Service\ApplicationService $sockAppService */
        $sockAppService = $this->container->get('digip_deploy.sock_api.application');
        /** @var DatabaseService $sockDbService */
        $sockDbService = $this->container->get('digip_deploy.sock_api.database');
        $queue = new PromiseQueue();

//        if ($app->getParent()) {
//            Messenger::send(
//                sprintf(
//                    'using existing account "%s" as parent on Sock Virtual Server %s', $app->getParent()->getName(), $server->getSockId()
//                )
//            );
//            $applicationName = $app->getName();
//        } else {
//            Messenger::send(
//                sprintf(
//                    'requesting account "%s" on Sock Virtual Server %s', $env->getServerSettings()->getUser(), $server->getSockId()
//                )
//            );
//            $queue->addPromise(
//                $envService
//                    ->createSockAccount($env, $server, $sockAccountService)
//                    ->then(function (EntityCreatePromise $promise) use ($env, $server) {
//                        Messenger::send(
//                            sprintf(
//                                $promise->getDidExist() ?
//                                    'account "%s" already exists on Sock Virtual Server %s' :
//                                    'account "%s" created on Sock Virtual Server %s', $env->getServerSettings()->getUser(), $server->getSockId()
//                            )
//                        );
//                    })
//                    ->error(function (EntityCreatePromise $promise) {
//                        $msg = 'error creating account';
//                        if ($promise->getPoller() && Poller::STATE_EXPIRED === $promise->getPoller()->getState()) {
//                            $msg .= ': polling event queue timed out';
//                        }
//                        Messenger::send($msg);
//
//                        throw new RuntimeException($msg);
//                    })
//            );
//
//            $applicationName = 'default';
//        }
//
//        Messenger::send(sprintf(
//                'requesting application "%s" on Sock Account %s', $applicationName, $env->getServerSettings()->getSockAccountId()
//        ));
//        $queue->addPromise(
//            $envService
//                ->createSockApplication($env, $sockAppService)
//                ->then(function (EntityCreatePromise $promise) use ($env, $server) {
//                    Messenger::send(
//                        sprintf(
//                            $promise->getDidExist() ?
//                                'application "%s" already exists on Sock Virtual Server %s' :
//                                'application "%s" created on Sock Virtual Server %s', $env->getServerSettings()->getUser(), $server->getSockId()
//                        )
//                    );
//                })
//                ->error(function (EntityCreatePromise $promise) {
//                    $msg = 'error creating application';
//                    if ($promise->getPoller() && Poller::STATE_EXPIRED === $promise->getPoller()->getState()) {
//                        $msg .= ': polling event queue timed out';
//                    }
//                    Messenger::send($msg);
//
//                    throw new RuntimeException($msg);
//                })
//        );
//
        if ($env->getApplication()->hasDatabase()) {
            Messenger::send(sprintf(
                    'requesting database "%s" on Sock Account %s', $env->getDatabaseSettings()->getName(), $env->getServerSettings()->getSockAccountId()
            ));
            $queue->addPromise(
                $envService
                    ->createSockDatabase($env, $sockDbService)
                    ->then(function (EntityCreatePromise $promise) use ($env, $server) {
                        Messenger::send(
                            sprintf(
                                $promise->getDidExist() ?
                                    'database "%s" already exists on Sock Virtual Server %s' :
                                    'database "%s" created on Sock Virtual Server %s', $env->getDatabaseSettings()->getName(), $server->getSockId()
                            )
                        );
                    })
                    ->error(function (EntityCreatePromise $promise) {
                        $msg = 'error creating database';
                        if ($promise->getPoller() && Poller::STATE_EXPIRED === $promise->getPoller()->getState()) {
                            $msg .= ': polling event queue timed out';
                        }
                        Messenger::send($msg);

                        throw new RuntimeException($msg);
                    })
            );
        }

        $queue->waitForResolution();
    }

    /**
     * Creates a background provision.
     * 
     * @param Application $app
     * @param int $provision
     *
     * @return Build
     */
    public function createNewBackgroundProvision(Application $app, $provision = self::PROVISION_ALL)
    {
        $build = new Build($app, Build::TYPE_PROVISION);
        $build->setUser($this->container->get('security.token_storage')->getToken()->getUser());
        $app->setProvisionBuild($build);
        $this->persist($build);

        $appId = $app->getId();
        $buildId = $build->getId();

        $options = '';
        if ($this->isEnabled($provision, self::PROVISION_ALL)) {
            $options .= 'a';
        } else {
            $types = [
                'C' => self::PROVISION_CRON,
                'J' => self::PROVISION_CI_OVERRIDE,
                'S' => self::PROVISION_SOCK,
                'c' => self::PROVISION_CONFIG_FILES,
                'f' => self::PROVISION_FILESYSTEM,
                'j' => self::PROVISION_CI,
            ];
            foreach ($types as $option => $type) {
                if ($this->isEnabled($provision, $type)) {
                    $options .= $option;
                }
            }
        }

        if ($options) {
            $options = '-' . $options;
        }

        // We mostly use this so we can mock the process builder and so we can
        // unit test this without actually executing the shell command.
        $this->processBuilder->setPrefix("exec php {$this->kernelDir}/../bin/console digip:provision -b$buildId $options -- $appId > /dev/null 2>&1 &");
        $this->processBuilder->getProcess()->run();

        return $build;
    }

    /**
     * Checks if $search bit is set in $mask.
     *
     * @param int $mask
     * @param int $search
     *
     * @return bool
     */
    protected function isEnabled($mask, $search)
    {
        return ($mask & $search) === $search;
    }
}
