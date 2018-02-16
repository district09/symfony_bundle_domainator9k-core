<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\QuuxApplication;
use PHPUnit\Framework\TestCase;

class ApplicationTypeTest extends TestCase
{

    public function testGetSettingImplementationName()
    {
        $this->assertEquals('application_type',ApplicationType::getSettingImplementationName());
    }

    public function testGettersAndSetters()
    {
        $applicationType = new ApplicationType();

        $applicationType->setName(QuuxApplication::getApplicationType());
        $this->assertEquals('quux_application',$applicationType->getName());

        $this->assertCount(0,$applicationType->getApplicationTypeEnvironments());

        $applicationTypeEnvironment = new ApplicationTypeEnvironment();
        $applicationType->addApplicationTypeEnvironment($applicationTypeEnvironment);

        $applicationTypeEnvironment = new ApplicationTypeEnvironment();
        $applicationType->addApplicationTypeEnvironment($applicationTypeEnvironment);

        $this->assertCount(2,$applicationType->getApplicationTypeEnvironments());

        $applicationType->removeApplicationTypeEnvironment($applicationTypeEnvironment);
        $this->assertCount(1,$applicationType->getApplicationTypeEnvironments());
    }
}
