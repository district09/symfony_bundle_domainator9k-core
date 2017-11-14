<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\EntityService;

use Ctrl\Common\EntityService\Finder\Doctrine\Finder;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironmentSettings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\DatabaseSettings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\EntityService\SettingsService;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Doctrine\ORM\EntityManager;
use ReflectionObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of SettingsServiceTest.
 *
 * @author Jelle Sebreghts
 */
class SettingsServiceTest extends TestCase
{
    use DataGenerator;

    protected $doctrine;

    protected function setUp()
    {
        parent::setUp();
        $this->doctrine = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
    }

    public function testGetEntityClass()
    {
        $service = $this->getService();
        $this->assertEquals(Settings::class, $service->getEntityClass());
    }

    public function testGetSettings()
    {
        $service = $this->getService();

        $settings = $this->getMockBuilder(Settings::class)->disableOriginalConstructor()->getMock();

        $finder = $this->getMockBuilder(Finder::class)->disableOriginalConstructor()->getMock();
        $finder->expects($this->once())->method('get')->with(1)->willReturn($settings);

        $refObject = new ReflectionObject($service);
        $refProperty = $refObject->getProperty('finder');
        $refProperty->setAccessible(true);
        $refProperty->setValue($service, $finder);

        $this->assertEquals($settings, $service->getSettings());
    }

    public function testApplyDefaults()
    {
        $service = $this->getService();

        $settings = $this->getMockBuilder(Settings::class)->disableOriginalConstructor()->getMock();

        $finder = $this->getMockBuilder(Finder::class)->disableOriginalConstructor()->getMock();
        $finder->expects($this->once())->method('get')->with(1)->willReturn($settings);

        $refObject = new ReflectionObject($service);
        $refProperty = $refObject->getProperty('finder');
        $refProperty->setAccessible(true);
        $refProperty->setValue($service, $finder);

        $host = $this->getAlphaNumeric();

        $dbSettings = $this->getMockBuilder(DatabaseSettings::class)->disableOriginalConstructor()->getMock();
        $dbSettings->expects($this->once())->method('setHost')->with($host);

        $env = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $env->expects($this->any())->method('getDatabaseSettings')->willReturn($dbSettings);

        $appEnvSettings = $this->getMockBuilder(AppEnvironmentSettings::class)->disableOriginalConstructor()->getMock();
        $appEnvSettings->expects($this->once())->method('getDatabaseHost')->willReturn($host);

        $settings->expects($this->once())->method('getAppEnvironmentSettings')->with($env)->willReturn($appEnvSettings);

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getAppEnvironments')->willReturn([$env]);

        $service->applyDefaults($app);
    }

    /**
     * @return SettingsService
     */
    protected function getService()
    {
        $service = new SettingsService();
        $service->setDoctrine($this->doctrine);

        return $service;
    }
}
