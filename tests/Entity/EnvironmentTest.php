<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{

    public function testGetSettingImplementationName()
    {
        $this->assertEquals('environment',Environment::getSettingImplementationName());
    }

    public function testGettersAndSetters()
    {
        $environment = new Environment();
        $environment->setName('prod');

        $this->assertEquals('prod',$environment->__toString());

        $this->assertNull($environment->isProd());
        $environment->setProd(true);
        $this->assertTrue($environment->isProd());
    }
}
