<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactoryInterface;
use InvalidArgumentException;

class Factory implements FactoryInterface
{

    protected $defaultOptions = array();

    /**
     * @var ShellFactoryInterface
     */
    protected $shellFactory;

    public function __construct(SshShellFactoryInterface $shellFactory)
    {
        $this->shellFactory = $shellFactory;
    }

    public function setDefaultOptions(array $options = array())
    {
        $this->defaultOptions = $options;
    }

    public function addTaskDefinition($class)
    {
        if (!is_subclass_of($class, TaskInterface::class)) {
            throw new InvalidArgumentException(sprintf(
                'Task %s does not implement %s.',
                $class,
                TaskInterface::class
            ));
        }
        $this->map[call_user_func([$class, 'getName'])] = $class;

        return $this;
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @return AbstractSshTask
     */
    public function create($name, array $options = array())
    {
        $class = $this->resolveTask($name);

        $task = new $class(array_merge($this->defaultOptions, $options));
        if (is_subclass_of($class, SshTaskInterface::class)) {
            $task->setSshShellFactory($this->shellFactory);
        }
        if (is_subclass_of($class, TaskFactoryAwareInterface::class)) {
            $task->setTaskFactory($this);
        }
        return $task;
    }

    protected function resolveTask($class)
    {
        if (!class_exists($class)) {
            if (!array_key_exists($class, $this->map)) {
                throw new InvalidArgumentException(sprintf(
                    'unknown task: %s',
                    $class
                ));
            }
            $class = $this->map[$class];
        }

        return $class;
    }

    /**
     * @return TaskRunner
     */
    public function createRunner()
    {
        $runner = new TaskRunner();

        return $runner;
    }
}
