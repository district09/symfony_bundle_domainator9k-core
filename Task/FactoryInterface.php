<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

interface FactoryInterface
{

    public static function setDefaultOptions(array $options = array());

    /**
     * @param string $name
     * @param array  $options
     *
     * @return DigipolisGent\Domainator9k\CoreBundle\Task\TaskInterface
     */
    public static function create($name, array $options = array());

    /**
     * @return DigipolisGent\Domainator9k\CoreBundle\Task\TaskRunnerInterface
     */
    public static function createRunner();
}