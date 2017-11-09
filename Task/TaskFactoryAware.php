<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

/**
 * Description of TaskFactoryAware.
 *
 * @author Jelle Sebreghts
 */
trait TaskFactoryAware
{

    /**
     * @var FactoryInterface
     */
    protected $taskFactory;

    /**
     * @param FactoryInterface $factory
     * @return $this
     */
    public function setTaskFactory(FactoryInterface $factory)
    {
        $this->taskFactory = $factory;

        return $this;
    }
}
