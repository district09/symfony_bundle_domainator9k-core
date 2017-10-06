<?php

namespace DigipolisGent\Domainator9k\CoreBundle\EntityService;

use Ctrl\Common\EntityService\AbstractDoctrineService;
use DigipolisGent\Domainator9k\CoreBundle\Interfaces\CiProcessorInterface;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Task\Messenger;
use DigipolisGent\SockAPIBundle\Service\Event\Poller;
use DigipolisGent\SockAPIBundle\Service\Promise\EntityCreatePromise;
use DigipolisGent\SockAPIBundle\Service\Promise\PromiseQueue;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @param string $workspaceDirectory
     * @param string $kernelDir
     */
    public function __construct($workspaceDirectory, $kernelDir)
    {
        $this->workspaceDirectory = $workspaceDirectory;
        $this->kernelDir = $kernelDir;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return 'DigipolisGent\Domainator9k\CoreBundle\Entity\Build';
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
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
     * @return string
     */
    public function getWorkspaceDirectory()
    {
        return $this->workspaceDirectory;
    }

    /**
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
     * @param Build  $build
     * @param string $newMessage
     * @param bool   $persist
     */
    public function updateBuildLog(Build $build, $newMessage, $persist = true)
    {
        $timestamp = str_pad(date('Y-m-d H:i:s'), 25);
        $newMessage = str_replace(PHP_EOL, PHP_EOL.str_repeat(' ', 25), $newMessage);
        $build->setLog(
            $build->getLog().PHP_EOL.$timestamp.$newMessage
        );

        if ($persist) {
            $this->persist($build);
        }
    }

    /**
     * @param Build    $build
     * @param array    $servers
     * @param Settings $settings
     * @param int      $provision
     *
     * @return bool
     *
     * @throws \RuntimeException
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
        } catch (\Exception $e) {
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
     * @param Build          $build
     * @param array|Server[] $servers
     * @param Settings       $settings
     * @param int            $options
     *
     * @return bool
     *
     * @throws \Exception
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
cron:               %s',
            $build->getApplication()->getName(),
            ($ciActive) ? 'On' : 'Off',
            ($this->isEnabled($options, self::PROVISION_CI_OVERRIDE)) ? 'On' : 'Off',
            ($allActive || $this->isEnabled($options, self::PROVISION_FILESYSTEM)) ? 'On' : 'Off',
            ($allActive || $this->isEnabled($options, self::PROVISION_CONFIG_FILES)) ? 'On' : 'Off',
            ($allActive || $this->isEnabled($options, self::PROVISION_SOCK)) ? 'On' : 'Off',
            (($allActive || $this->isEnabled($options, self::PROVISION_CRON)) && $allowPartialBuilds) ? 'On' : 'Off'
        ));

        if (!$allActive && !$allowPartialBuilds && $this->container->getParameter('kernel.environment') !== 'dev') {
            Messenger::send('a partial build was requested, but is seems we are missing required parts');
            throw new \RuntimeException('Partial build not possible');
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
                if ($envService->createCronJob($env, $envServers)) {
                    Messenger::send('cron jobs installed');
                } else {
                    Messenger::send('no cron jobs installed');
                }
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
                                $app->getProdAppEnvironment(),
                                $envServers,
                                $ciOverrideActive
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
                $settings,
                $app,
                $servers
            );
        }

        $build->setCompleted(true);
        $build->setSuccess(true);
        $this->persist($build);

        return true;
    }

    /**
     * @param string $text
     * @param string $padChar
     *
     * @return string
     */
    protected function getCliHeader($text, $padChar = '=')
    {
        return PHP_EOL.str_pad(str_repeat($padChar, 20).' '.trim($text).' ', 80, $padChar);
    }

    /**
     * @param AppEnvironment $env
     * @param Server         $server
     *
     * @throws \Exception
     */
    protected function executeSockProvision(AppEnvironment $env, Server $server)
    {
        $app = $env->getApplication();

        $envService = $this->container->get('digip_deploy.entity.appenvironment');
        $sockAccountService = $this->container->get('digip_deploy.sock_api.account');
        $sockAppService = $this->container->get('digip_deploy.sock_api.application');
        $sockDbService = $this->container->get('digip_deploy.sock_api.database');
        $queue = new PromiseQueue();

        if ($app->getParent()) {
            Messenger::send(
                sprintf(
                    'using existing account "%s" as parent on Sock Virtual Server %s',
                    $app->getParent()->getName(),
                    $server->getSockId()
                )
            );
            $applicationName = $app->getName();
        } else {
            Messenger::send(
                sprintf(
                    'requesting account "%s" on Sock Virtual Server %s',
                    $env->getServerSettings()->getUser(),
                    $server->getSockId()
                )
            );
            $queue->addPromise(
                $envService
                    ->createSockAccount($env, $server, $sockAccountService)
                    ->then(function (EntityCreatePromise $promise) use ($env, $server) {
                        Messenger::send(
                            sprintf(
                                $promise->getDidExist() ?
                                    'account "%s" already exists on Sock Virtual Server %s' :
                                    'account "%s" created on Sock Virtual Server %s',
                                $env->getServerSettings()->getUser(),
                                $server->getSockId()
                            )
                        );
                    })
                    ->error(function (EntityCreatePromise $promise) {
                        $msg = 'error creating account';
                        if ($promise->getPoller() && $promise->getPoller()->getState() === Poller::STATE_EXPIRED) {
                            $msg .= ': polling event queue timed out';
                        }
                        Messenger::send($msg);
                        throw new \RuntimeException($msg);
                    })
            );

            $applicationName = 'default';
        }

        Messenger::send(sprintf(
            'requesting application "%s" on Sock Account %s',
            $applicationName,
            $env->getServerSettings()->getSockAccountId()
        ));
        $queue->addPromise(
            $envService
                ->createSockApplication($env, $sockAppService)
                ->then(function (EntityCreatePromise $promise) use ($env, $server) {
                    Messenger::send(
                        sprintf(
                            $promise->getDidExist() ?
                                'application "%s" already exists on Sock Virtual Server %s' :
                                'application "%s" created on Sock Virtual Server %s',
                            $env->getServerSettings()->getUser(),
                            $server->getSockId()
                        )
                    );
                })
                ->error(function (EntityCreatePromise $promise) {
                    $msg = 'error creating application';
                    if ($promise->getPoller() && $promise->getPoller()->getState() === Poller::STATE_EXPIRED) {
                        $msg .= ': polling event queue timed out';
                    }
                    Messenger::send($msg);
                    throw new \RuntimeException($msg);
                })
        );

        if ($env->getApplication()->hasDatabase()) {
            Messenger::send(sprintf(
                'requesting database "%s" on Sock Account %s',
                $env->getDatabaseSettings()->getName(),
                $env->getServerSettings()->getSockAccountId()
            ));
            $queue->addPromise(
                $envService
                    ->createSockDatabase($env, $sockDbService)
                    ->then(function (EntityCreatePromise $promise) use ($env, $server) {
                        Messenger::send(
                            sprintf(
                                $promise->getDidExist() ?
                                    'database "%s" already exists on Sock Virtual Server %s' :
                                    'database "%s" created on Sock Virtual Server %s',
                                $env->getDatabaseSettings()->getName(),
                                $server->getSockId()
                            )
                        );
                    })
                    ->error(function (EntityCreatePromise $promise) {
                        $msg = 'error creating database';
                        if ($promise->getPoller() && $promise->getPoller()->getState() === Poller::STATE_EXPIRED) {
                            $msg .= ': polling event queue timed out';
                        }
                        Messenger::send($msg);
                        throw new \RuntimeException($msg);
                    })
            );
        }

        $queue->waitForResolution();
    }

    /**
     * @param Application $app
     * @param int         $provision
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

        $options = [];
        if ($this->isEnabled($provision, self::PROVISION_ALL)) {
            $options[] = 'a';
        } else {
            if ($this->isEnabled($provision, self::PROVISION_CI)) {
                $options[] = 'j';
            }
            if ($this->isEnabled($provision, self::PROVISION_CI_OVERRIDE)) {
                $options[] = 'J';
            }
            if ($this->isEnabled($provision, self::PROVISION_FILESYSTEM)) {
                $options[] = 'f';
            }
            if ($this->isEnabled($provision, self::PROVISION_CONFIG_FILES)) {
                $options[] = 'c';
            }
            if ($this->isEnabled($provision, self::PROVISION_SOCK)) {
                $options[] = 'S';
            }
            if ($this->isEnabled($provision, self::PROVISION_CRON)) {
                $options[] = 'C';
            }
        }

        $options = implode('', $options);
        if ($options) {
            $options = '-'.$options;
        }

        //leave this comment pls ?
        exit("exec php {$this->kernelDir}/../bin/console digip:provision -b$buildId $options -- $appId > /dev/null 2>&1 &");

        shell_exec("exec php {$this->kernelDir}/../bin/console digip:provision -b$buildId $options -- $appId > /dev/null 2>&1 &");

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
