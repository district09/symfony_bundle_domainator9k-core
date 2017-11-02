<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;

interface TaskInterface
{

    public function getName();

    /**
     * @return TaskResult
     */
    public function getNewResult();

    /**
     * Execute a task.
     *
     * @return TaskResult
     */
    public function execute();

    /**
     * Revert a task.
     *
     * @return bool
     */
    public function revert();

    /**
     * @return bool
     */
    public function isExecuted();

    /**
     * @return AppEnvironment
     */
    public function getAppEnvironment();

    /**
     * @return string
     */
    public function getHomeDirectory();
}
