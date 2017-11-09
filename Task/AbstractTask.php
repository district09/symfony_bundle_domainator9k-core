<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractTask implements TaskInterface
{

    /**
     * @var bool
     */
    protected $executed = false;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var AppEnvironment
     */
    protected $appEnvironment;

    /**
     * Creates a new task.
     *
     * @param array $options
     *     Options for this task.
     */
    public function __construct(array $options = array())
    {
        $optionsResolver = new OptionsResolver();
        $this->configure($optionsResolver);
        $this->options = $optionsResolver->resolve($options);

        if ($optionsResolver->isDefined('appEnvironment')) {
            $this->appEnvironment = $this->options['appEnvironment'];
        }
    }

    /**
     * Configure options for this task.
     *
     * @param OptionsResolver $options
     */
    protected function configure(OptionsResolver $options)
    {
        $options->setRequired(array(
            'appEnvironment',
        ));

        $options->setAllowedTypes('appEnvironment', ['DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment']);
    }

    /**
     * @return TaskResult
     */
    public function getNewResult()
    {
        $result = new TaskResult();

        return $result;
    }

    /**
     * Execute a task.
     *
     * @return TaskResult
     */
    public function execute()
    {
        $this->executed = true;

        return $this->getNewResult();
    }

    /**
     * Revert a task.
     *
     * @return bool
     */
    public function revert()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isExecuted()
    {
        return $this->executed;
    }

    /**
     * @return AppEnvironment
     */
    public function getAppEnvironment()
    {
        return $this->appEnvironment;
    }

    /**
     * @return string
     */
    public function getHomeDirectory()
    {
        if (function_exists('posix_getpwuid')) {
            $info = posix_getpwuid(posix_getuid());

            return $info['dir'];
        }

        return isset($_SERVER['HOME']) ? $_SERVER['HOME'] : realpath('~');
    }
}
