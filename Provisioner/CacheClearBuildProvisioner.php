<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Exception\NoCacheClearerFoundException;
use DigipolisGent\Domainator9k\CoreBundle\Exception\NoCliFactoryFoundException;
use DigipolisGent\Domainator9k\CoreBundle\Provider\CacheClearProvider;
use DigipolisGent\Domainator9k\CoreBundle\Provider\CliFactoryProvider;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskLoggerService;

class CacheClearBuildProvisioner extends AbstractProvisioner
{

    /**
     * @var CliFactoryProvider
     */
    protected $cliFactoryProvider;

    /**
     * @var CacheClearProvider
     */
    protected $cacheClearProvider;

    /**
     * @var TaskLoggerService
     */
    protected $taskLoggerService;

    public function __construct(
        CliFactoryProvider $cliFactoryProvider,
        CacheClearProvider $cacheClearProvider,
        TaskLoggerService $taskLoggerService
    ) {
        $this->cliFactoryProvider = $cliFactoryProvider;
        $this->cacheClearProvider = $cacheClearProvider;
        $this->taskLoggerService = $taskLoggerService;
    }

    protected function doRun()
    {
        $appEnv = $this->task->getApplicationEnvironment();
        $application = $appEnv->getApplication();
        $environment = $appEnv->getEnvironment();

        $this->taskLoggerService->addLogHeader(
            $this->task,
            sprintf(
                'Clearing cache for %s on %s.',
                $application->getName(),
                $environment->getName()
            )
        );
        try {
            $this->cacheClearProvider
                ->getCacheClearerFor($application)
                ->clearCache(
                    $appEnv,
                    $this->cliFactoryProvider->createCliFor($appEnv)
                );
        } catch (NoCacheClearerFoundException $cacheEx) {
            $this->taskLoggerService->addWarningLogMessage($this->task, $cacheEx->getMessage(), 2);
        } catch (NoCliFactoryFoundException $cliEx) {
            $this->taskLoggerService->addWarningLogMessage($this->task, $cliEx->getMessage(), 2);
        }
    }

    public function getName()
    {
        return 'Clear caches';
    }
}
