<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactoryInterface;

interface TaskInterface
{

    public static function getName();

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

    /**
     * @param SshShellFactoryInterface $sshShellFactory
     */
    public function setShellFactory(SshShellFactoryInterface $sshShellFactory);
}
