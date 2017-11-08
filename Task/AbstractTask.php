<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractTask implements TaskInterface
{

    /**
     * @var bool
     */
    protected $executed = false;
    protected $options = array();

    /**
     * @var SshShellFactoryInterface
     */
    protected $sshShellFactory;

    /**
     * @var AppEnvironment
     */
    protected $appEnvironment;

    public function __construct(array $options = array())
    {
        $optionsResolver = new OptionsResolver();
        $this->configure($optionsResolver);
        $this->options = $optionsResolver->resolve($options);

        if ($optionsResolver->isDefined('appEnvironment')) {
            $this->appEnvironment = $this->options['appEnvironment'];
        }
    }

    protected function configure(OptionsResolver $options)
    {
        $options->setRequired(array(
            'appEnvironment',
        ));

        $options->setAllowedTypes('appEnvironment', ['DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment']);
    }

    public static function getName()
    {
        throw new \LogicException(
            sprintf(
                'Classes implementing %s should override the static getName() method.',
                AbstractTask::class
            )
        );
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

    /**
     * @param SshShellFactoryInterface $sshShellFactory
     *
     * @return $this
     */
    public function setShellFactory(SshShellFactoryInterface $sshShellFactory)
    {
        $this->sshShellFactory = $sshShellFactory;

        return $this;
    }
}
