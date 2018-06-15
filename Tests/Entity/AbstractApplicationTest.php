<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\QuuxApplication;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class AbstractApplicationTest extends TestCase
{

    public function testGetTemplateReplacements()
    {
        $expected = [
            'name()' => 'getName()',
            'nameCanonical()' => 'getNameCanonical()',
            'gitRepo()' => 'getGitRepo()',
            'config(key)' => 'getConfig(key)',
            'applicationEnvironmentDatabaseName(name)' => 'getApplicationEnvironmentByEnvironmentName(name).getDatabaseName()',
            'applicationEnvironmentEnvironmentName(name)' => 'getApplicationEnvironmentByEnvironmentName(name).getEnvironmentName()',
            'applicationEnvironmentEnvironmentGitRef(name)' => 'getApplicationEnvironmentByEnvironmentName(name).getEnvironment().getGitRef()',
            'applicationEnvironmentEnvironmentConfig(name,key)' => 'getApplicationEnvironmentByEnvironmentName(name).getEnvironment().getConfig(key)',
            'applicationEnvironmentEnvironmentPriority(name)' => 'getApplicationEnvironmentByEnvironmentName(name).getEnvironment().getPriority()',
            'applicationEnvironmentDatabaseUser(name)' => 'getApplicationEnvironmentByEnvironmentName(name).getDatabaseUser()',
            'applicationEnvironmentDatabasePassword(name)' => 'getApplicationEnvironmentByEnvironmentName(name).getDatabasePassword()',
            'applicationEnvironmentGitRef(name)' => 'getApplicationEnvironmentByEnvironmentName(name).getGitRef()',
            'applicationEnvironmentDomain(name)' => 'getApplicationEnvironmentByEnvironmentName(name).getDomain()',
            'applicationEnvironmentServerIps(name)' => 'getApplicationEnvironmentByEnvironmentName(name).getServerIps()',
            'applicationEnvironmentWorkerServerIp(name)' => 'getApplicationEnvironmentByEnvironmentName(name).getWorkerServerIp()',
            'applicationEnvironmentConfig(name,key)' => 'getApplicationEnvironmentByEnvironmentName(name).getConfig(key)',
            'serverIps(dev_environment_name)' => 'getApplicationEnvironmentByEnvironmentName(dev_environment_name).getServerIps()',
        ];

        $this->assertEquals($expected, QuuxApplication::getTemplateReplacements());
    }

    public function testGetApplicationEnvironmentByEnvironmentName()
    {
        $environment = new Environment();
        $environment->setName('prod');

        $applicationEnvironment = new ApplicationEnvironment();
        $applicationEnvironment->setEnvironment($environment);

        $application = new QuuxApplication();
        $application->addApplicationEnvironment($applicationEnvironment);

        $result = $application->getApplicationEnvironmentByEnvironmentName('prod');
        $this->assertEquals($applicationEnvironment, $result);

        $result = $application->getApplicationEnvironmentByEnvironmentName('random');
        $this->assertEquals('', $result);
    }

    public function testGettersAndSetters()
    {
        $application = new QuuxApplication();

        $application->setName('My application name');
        $this->assertEquals('My application name', $application->getName());
        $this->assertEquals('myapplicationn', $application->getNameCanonical());

        $this->assertTrue($application->isHasDatabase());
        $application->setHasDatabase(false);
        $this->assertFalse($application->isHasDatabase());

        $this->assertEquals('application', $application::getSettingImplementationName());

        $application->setGitRepo('my-git-repo');
        $this->assertEquals('my-git-repo',$application->getGitRepo());

        $applicationEnvironment = new ApplicationEnvironment();
        $application->addApplicationEnvironment($applicationEnvironment);
        $this->assertCount(1,$application->getApplicationEnvironments());
        $application->removeApplicationEnvironment($applicationEnvironment);
        $this->assertCount(0,$application->getApplicationEnvironments());

        $this->assertNull($application->getId());

        $application->setDeleted(true);
        $this->assertTrue($application->isDeleted());
    }
}
