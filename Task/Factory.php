<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

class Factory implements FactoryInterface
{
    protected static $map = array(
        'provision.filesystem' => '\DigipolisGent\Domainator9k\CoreBundle\Task\Provision\Filesystem',
        'provision.config_files' => '\DigipolisGent\Domainator9k\CoreBundle\Task\Provision\ConfigFiles',
        'provision.cron' => '\DigipolisGent\Domainator9k\CoreBundle\Task\Provision\Cron',
        'filesystem.create_directory' => '\DigipolisGent\Domainator9k\CoreBundle\Task\Filesystem\CreateDirectory',
        'filesystem.create_file' => '\DigipolisGent\Domainator9k\CoreBundle\Task\Filesystem\CreateFile',
        'filesystem.link' => '\DigipolisGent\Domainator9k\CoreBundle\Task\Filesystem\Link',
        //'console.jenkins'               => '\DigipolisGent\Domainator9k\CoreBundle\Task\Console\Jenkins',
        'console.cron' => '\DigipolisGent\Domainator9k\CoreBundle\Task\Console\Cron',
    );

    protected static $defaultOptions = array();

    public static function setDefaultOptions(array $options = array())
    {
        self::$defaultOptions = $options;
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @return AbstractTask
     */
    public static function create($name, array $options = array())
    {
        if (class_exists($name)) {
            return new $name(array_merge(self::$defaultOptions, $options));
        }

        if (!array_key_exists($name, self::$map)) {
            throw new \InvalidArgumentException(sprintf(
                'unknown task: %s', $name
            ));
        }

        $options = array_merge(self::$defaultOptions, $options);

        $class = self::$map[$name];

        return new $class($options);
    }

    /**
     * @return TaskRunner
     */
    public static function createRunner()
    {
        $runner = new TaskRunner();

        return $runner;
    }
}
