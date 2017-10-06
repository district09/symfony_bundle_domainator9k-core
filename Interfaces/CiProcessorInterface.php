<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Interfaces;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;

interface CiProcessorInterface
{
    /**
     * @param Application $app
     * @param bool        $override
     *
     * @return mixed
     */
    public function createValidateJob(Application $app, $override = false);

    /**
     * @param AppEnvironment $appEnvironment
     * @param array          $servers
     * @param bool           $override
     *
     * @return mixed
     */
    public function createDeployJob(AppEnvironment $appEnvironment, array $servers, $override = false);

    /**
     * @param AppEnvironment $appEnvironment
     * @param array          $servers
     * @param bool           $override
     *
     * @return mixed
     */
    public function createRevertJob(AppEnvironment $appEnvironment, array $servers, $override = false);

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param AppEnvironment $appEnvironmentFrom
     * @param AppEnvironment $appEnvironmentTo
     * @param array          $servers
     * @param bool           $override
     *
     * @return
     */
    public function createSyncJob(AppEnvironment $appEnvironmentFrom, AppEnvironment $appEnvironmentTo, array $servers, $override = false);

    /**
     * @param AppEnvironment $appEnvironment
     * @param array          $servers
     * @param bool           $override
     *
     * @return mixed
     */
    public function createDumpJob(AppEnvironment $appEnvironment, array $servers, $override = false);
}
