<?php

namespace DigipolisGent\Domainator9k\CoreBundle\EntityService;

use Ctrl\Common\EntityService\AbstractDoctrineService;

class ApplicationTypeService extends AbstractDoctrineService
{
    /**
     * @return string
     */
    public function getEntityClass()
    {
        return 'DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType';
    }
}
