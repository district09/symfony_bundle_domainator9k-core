<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use PHPUnit\Framework\TestCase;

class ApplicationTypeEnvironmentTest extends TestCase
{

    public function testGetSettingImplementationName()
    {
        $actual = ApplicationTypeEnvironment::getSettingImplementationName();
        $this->assertEquals('application_type_environment',$actual);
    }

    public function testGettersAndSetters()
    {
        $applicationTypeEnvironment = new ApplicationTypeEnvironment();

        $environment = new Environment();
        $environment->setName('prod');

        $applicationTypeEnvironment->setEnvironment($environment);
        $this->assertEquals($environment,$applicationTypeEnvironment->getEnvironment());
        $this->assertEquals('prod',$applicationTypeEnvironment->getEnvironmentName());

        $applicationType = new ApplicationType();
        $applicationTypeEnvironment->setApplicationType($applicationType);
        $this->assertEquals($applicationType,$applicationTypeEnvironment->getApplicationType());
    }
}
