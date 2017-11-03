<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\EntityService;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\EntityService\ServerService;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of ServerServiceTest
 *
 * @author Jelle Sebreghts
 */
class ServerServiceTest extends TestCase
{

    public function testGetEntityClass()
    {
        $service = $this->getService();
        $this->assertEquals(Server::class, $service->getEntityClass());
    }


    /**
     *
     * @return ServerService
     */
    protected function getService()
    {
        return new ServerService();
    }

}
