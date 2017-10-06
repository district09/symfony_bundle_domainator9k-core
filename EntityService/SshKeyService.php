<?php

namespace DigipolisGent\Domainator9k\CoreBundle\EntityService;

use Ctrl\Common\EntityService\AbstractDoctrineService;

class SshKeyService extends AbstractDoctrineService
{
    /**
     * @return string
     */
    public function getEntityClass()
    {
        return 'DigipolisGent\Domainator9k\CoreBundle\Entity\SshKey';
    }
}
