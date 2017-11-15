<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\PathUtil\Path;

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
     * @var string
     */
    protected $homeDirectory;

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
     * @param mixed $dir
     *
     * @return $this
     */
    public function setHomeDirectory($dir)
    {
        $this->homeDirectory = $dir;

        return $this;
    }

    /**
     * @return string
     */
    public function getHomeDirectory()
    {
        if (null !== $this->homeDirectory) {
            return $this->homeDirectory;
        }

        return Path::getHomeDirectory();
    }
}
