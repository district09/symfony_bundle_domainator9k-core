<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Exception\NoCacheClearerFoundException;
use DigipolisGent\Domainator9k\CoreBundle\Exception\NoCliFactoryFoundException;
use DigipolisGent\Domainator9k\CoreBundle\Provider\CacheClearProvider;
use DigipolisGent\Domainator9k\CoreBundle\Provider\CliFactoryProvider;

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

    public function __construct(CliFactoryProvider $cliFactoryProvider, CacheClearProvider $cacheClearProvider)
    {
        $this->cliFactoryProvider = $cliFactoryProvider;
        $this->cacheClearProvider = $cacheClearProvider;
    }

    protected function doRun()
    {
        $appEnv = $this->task->getApplicationEnvironment();
        try {
            $this->cacheClearProvider
                ->getCacheClearerFor($appEnv->getApplication())
                ->clearCache(
                    $appEnv,
                    $this->cliFactoryProvider->createCliFor($appEnv)
                );
        }
        catch (NoCacheClearerFoundException $cacheEx) {
            // There is no cache clearer registered for this application type,
            // meaning we can't clear cache for it. This probably shouldn't make
            // the task fail, but should we log it somehow?
        }
        catch (NoCliFactoryFoundException $cliEx) {
            // There is no cli factory registered for this application
            // envrironment, meaning we can't clear cache for it. This probably
            // shouldn't make the task fail, but should we log it somehow?
        }
    }

    public function getName()
    {
        return 'Clear caches';
    }
}
