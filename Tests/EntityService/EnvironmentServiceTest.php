<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\EntityService;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\EntityService\EnvironmentService;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of AppEnvironmentServiceTest.
 *
 * @author Jelle Sebreghts
 */
class EnvironmentServiceTest extends TestCase
{
    public function testGetEntityClass()
    {
        $service = $this->getService();
        $this->assertEquals(Environment::class, $service->getEntityClass());
    }

    /**
     * @return EnvironmentService
     */
    protected function getService()
    {
        return new EnvironmentService();
    }
}
