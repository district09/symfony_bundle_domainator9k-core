<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Stub\TaskFactory;

use DigipolisGent\Domainator9k\CoreBundle\Task\FactoryInterface;
use Exception;

/**
 * Description of TestTaskFactory
 *
 * @author Jelle Sebreghts
 */
class TestTaskFactory implements FactoryInterface
{

    protected static $runner;
    protected static $task;
    protected static $defaultOptions;
    protected static $expectedArguments;

    public static function setExpectedArguments($expectedArguments)
    {
        self::$expectedArguments = $expectedArguments;
    }

    public static function setRunner($runner)
    {
        static::$runner = $runner;
    }

    public static function setTask($task)
    {
        static::$task = $task;
    }

    public static function create($name, array $options = array())
    {
        if (static::$expectedArguments != [$name, $options]) {
            throw new Exception('Expected arguments ' . print_r(static::$expectedArguments, true) . ' got ' . print_r(func_get_args(), true));
        }
        return static::$task;
    }

    public static function createRunner()
    {
        return static::$runner;
    }

    public static function setDefaultOptions(array $options = array())
    {
        static::$defaultOptions = $options;
    }

}
