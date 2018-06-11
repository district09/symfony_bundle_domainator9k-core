<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;

interface ProvisionerInterface
{
    public function run(Task $task);
    public function getName();
}
