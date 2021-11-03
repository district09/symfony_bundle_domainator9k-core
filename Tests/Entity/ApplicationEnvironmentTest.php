<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\QuuxApplication;
use DigipolisGent\SettingBundle\Entity\SettingDataType;
use DigipolisGent\SettingBundle\Entity\SettingDataValue;
use PHPUnit\Framework\TestCase;

class ApplicationEnvironmentTest extends TestCase
{

    public function testGetSettingImplementationName()
    {
        $this->assertEquals('application_environment', ApplicationEnvironment::getSettingImplementationName());
    }

    public function testTemplateReplacements()
    {
        $expected = [
            'serverIps()' => 'getServerIps()',
            'workerServerIp()' => 'getWorkerServerIp()',
            'environmentName()' => 'getEnvironmentName()',
            'applicationName()' => 'getApplication().getName()',
            'applicationNameCanonical()' => 'getApplication().getNameCanonical()',
            'applicationGitRepo()' => 'getApplication().getGitRepo()',
            'applicationServerIps(dev_environment_name)' => 'getApplication().getApplicationEnvironmentByEnvironmentName(dev_environment_name).getServerIps()',
            'environmentGitRef()' => 'getEnvironment().getGitRef()',
            'environmentConfig(key)' => 'getEnvironment().getConfig(key)',
            'environmentPriority()' => 'getEnvironment().getPriority()',
            'config(key)' => 'getConfig(key)',
            'databaseName()' => 'getDatabaseName()',
            'databaseUser()' => 'getDatabaseUser()',
            'databasePassword()' => 'getDatabasePassword()',
            'gitRef()' => 'getGitRef()',
            'domain()' => 'getDomain()',
            'applicationConfig(key)' => 'getApplication().getConfig(key)',
            'applicationId()' => 'getApplication().getId()',
            'environmentId()' => 'getEnvironment().getId()',
            'id()' => 'getId()',
        ];

        $this->assertEquals($expected, ApplicationEnvironment::getTemplateReplacements());
    }

    public function testGettersAndSetters()
    {
        $applicationEnvironment = new ApplicationEnvironment();

        $application = new QuuxApplication();
        $applicationEnvironment->setApplication($application);
        $this->assertEquals($application, $applicationEnvironment->getApplication());

        $environment = new Environment();
        $environment->setName('prod');

        $applicationEnvironment->setEnvironment($environment);
        $this->assertEquals($environment, $applicationEnvironment->getEnvironment());
        $this->assertEquals('prod', $applicationEnvironment->getEnvironmentName());

        $applicationEnvironment->setDatabaseName('database-name');
        $this->assertEquals('database-name', $applicationEnvironment->getDatabaseName());

        $applicationEnvironment->setDatabaseUser('database-user');
        $this->assertEquals('database-user', $applicationEnvironment->getDatabaseUser());

        $applicationEnvironment->setDatabasePassword('database-password');
        $this->assertEquals('database-password', $applicationEnvironment->getDatabasePassword());

        $applicationEnvironment->setGitRef('git-ref');
        $this->assertEquals('git-ref', $applicationEnvironment->getGitRef());

        $applicationEnvironment->setDomain('domain');
        $this->assertEquals('domain', $applicationEnvironment->getDomain());

        $this->assertCount(0, $applicationEnvironment->getTasks());

        $settingDataType = new SettingDataType();
        $settingDataType->setKey('existing-key');

        $settingValue = new SettingDataValue();
        $settingValue->setValue('value');
        $settingValue->setSettingDataType($settingDataType);

        $applicationEnvironment->addSettingDataValue($settingValue);

        $this->assertEquals('',$applicationEnvironment->getConfig('random-key'));
        $this->assertEquals('value',$applicationEnvironment->getConfig('existing-key'));

        $environment = new Environment();
        $environment->setName('uat');

        $server = new VirtualServer();
        $server->setHost('0.0.0.0');
        $environment->addVirtualServer($server);

        $server = new VirtualServer();
        $server->setHost('1.1.1.1');
        $environment->addVirtualServer($server);

        $applicationEnvironment->setEnvironment($environment);

        $this->assertEquals('0.0.0.0 1.1.1.1',$applicationEnvironment->getServerIps());
    }
}
