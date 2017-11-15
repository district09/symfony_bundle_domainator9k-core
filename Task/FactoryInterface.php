<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

interface FactoryInterface
{
    /**
     * Set the default options for this task.
     *
     * @param array $options
     *
     * @return $this
     */
    public function setDefaultOptions(array $options = array());

    /**
     * Get the default options for this task.
     *
     * @return array
     */
    public function getDefaultOptions();

    /**
     * @param string $name
     * @param array  $options
     *
     * @return DigipolisGent\Domainator9k\CoreBundle\Task\TaskInterface
     */
    public function create($name, array $options = array());

    /**
     * @return DigipolisGent\Domainator9k\CoreBundle\Task\TaskRunnerInterface
     */
    public function createRunner();
}
