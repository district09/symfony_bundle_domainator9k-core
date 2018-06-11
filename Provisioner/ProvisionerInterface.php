<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;

interface ProvisionerInterface
{
    public function setTask(Task $task);
    public function run();
    public function getName();
}
