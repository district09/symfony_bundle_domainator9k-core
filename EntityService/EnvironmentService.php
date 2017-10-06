<?php

namespace DigipolisGent\Domainator9k\CoreBundle\EntityService;

use Ctrl\Common\EntityService\AbstractDoctrineService;

class EnvironmentService extends AbstractDoctrineService
{
    /**
     * @return string
     */
    public function getEntityClass()
    {
        return 'DigipolisGent\Domainator9k\CoreBundle\Entity\Environment';
    }
}
