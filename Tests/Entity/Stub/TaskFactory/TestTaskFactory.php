<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Stub\TaskFactory;

use DigipolisGent\Domainator9k\CoreBundle\Task\FactoryInterface;
use Exception;

/**
 * Description of TestTaskFactory.
 *
 * @author Jelle Sebreghts
 */
class TestTaskFactory implements FactoryInterface
{
    protected $runner;
    protected $task;
    protected $defaultOptions;
    protected $expectedArguments;

    public function setExpectedArguments($expectedArguments)
    {
        $this->expectedArguments = $expectedArguments;
    }

    public function setRunner($runner)
    {
        $this->runner = $runner;
    }

    public function setTask($task)
    {
        $this->task = $task;
    }

    public function create($name, array $options = array())
    {
        if ($this->expectedArguments != [$name, $options]) {
            throw new Exception('Expected arguments ' . print_r($this->expectedArguments, true) . ' got ' . print_r(func_get_args(), true));
        }

        return $this->task;
    }

    public function createRunner()
    {
        return $this->runner;
    }

    public function setDefaultOptions(array $options = array())
    {
        $this->defaultOptions = $options;

        return $this;
    }

    public function getDefaultOptions()
    {
        return $this->defaultOptions;
    }
}
