<?php

namespace DigipolisGent\Domainator9k\CoreBundle\EntityService;

use Ctrl\Common\EntityService\AbstractDoctrineService;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;

class ServerService extends AbstractDoctrineService
{
    /**
     * @return string
     */
    public function getEntityClass()
    {
        return Server::class;
    }
}
