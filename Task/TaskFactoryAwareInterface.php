<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

/**
 * Description of TaskFactoryAwareInterface.
 *
 * @author Jelle Sebreghts
 */
interface TaskFactoryAwareInterface
{

    /**
     * @param FactoryInterface $factory
     * @return $this
     */
    public function setTaskFactory(FactoryInterface $factory);
}
