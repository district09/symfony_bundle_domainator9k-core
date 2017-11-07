<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

/**
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
    public function setTaskFactory(FactoryInterface $factory) {
        $this->taskFactory = $factory;

        return $this;
    }
}
