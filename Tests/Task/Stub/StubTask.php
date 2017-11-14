<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Task\Stub;

use DigipolisGent\Domainator9k\CoreBundle\Task\TaskInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskResult;

/**
 * Description of StubTask
 *
 * @author Jelle Sebreghts
 */
class StubTask implements TaskInterface
{

    protected $revertResult;
    protected $isExecuted;
    protected $executeResult;

    public function setRevertResult($result)
    {
        $this->revertResult = $result;

        return $this;
    }

    public function setIsExecuted($executed)
    {
        $this->isExecuted = $executed;

        return $this;
    }

    public function setExecuteResult($executeResult)
    {
        $this->executeResult = $executeResult;

        return $this;
    }

    //put your code here
    public function execute()
    {
        return $this->executeResult;
    }

    public function getAppEnvironment()
    {
        return null;
    }

    public function getHomeDirectory()
    {
        return '';
    }

    public function getNewResult()
    {
        return new TaskResult();
    }

    public function isExecuted()
    {
        return $this->isExecuted;
    }

    public function revert()
    {
        return $this->revertResult;
    }

    public static function getName()
    {
        return 'stub.task';
    }
}
