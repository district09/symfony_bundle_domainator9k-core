<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\BaseAppType;
use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Service\AppTypeSettingsService;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of AppTypeSettingsServiceTest
 *
 * @author Jelle Sebreghts
 */
class AppTypeSettingsServiceTest extends TestCase
{

    use DataGenerator;

    protected $registry;
    protected $applicationTypeBuilder;

    protected function setUp()
    {
        parent::setUp();
        $this->registry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
        $this->applicationTypeBuilder = $this->getMockBuilder(ApplicationTypeBuilder::class)->disableOriginalConstructor()->getMock();
    }

    public function testGetSettings()
    {
        $service = $this->getService();

        $slug = $this->getAlphaNumeric();

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getAppTypeSlug')->willReturn($slug);

        $settingsClass = $this->getAlphaNumeric();

        $appType = $this->getMockBuilder(BaseAppType::class)->disableOriginalConstructor()->getMock();
        $appType->expects($this->once())->method('getSettingsEntityClass')->willReturn($settingsClass);

        $this->applicationTypeBuilder->expects($this->once())->method('getType')->with($slug)->willReturn($appType);

        $settings = new \stdClass();

        $repository = $this->getMockBuilder(ObjectRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects($this->once())->method('findOneBy')->with(['application' => $app])->willReturn($settings);

        $manager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $manager->expects($this->once())->method('getRepository')->with($settingsClass)->willReturn($repository);

        $this->registry->expects($this->once())->method('getManager')->willReturn($manager);

        $this->assertEquals($settings, $service->getSettings($app));
    }

    public function testGetSettingsFallback()
    {
        $service = $this->getService();

        $slug = $this->getAlphaNumeric();

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getAppTypeSlug')->willReturn($slug);

        $settingsClass = '\stdClass';

        $appType = $this->getMockBuilder(BaseAppType::class)->disableOriginalConstructor()->getMock();
        $appType->expects($this->once())->method('getSettingsEntityClass')->willReturn($settingsClass);

        $this->applicationTypeBuilder->expects($this->once())->method('getType')->with($slug)->willReturn($appType);

        $settings = false;

        $repository = $this->getMockBuilder(ObjectRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects($this->once())->method('findOneBy')->with(['application' => $app])->willReturn($settings);

        $manager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $manager->expects($this->once())->method('getRepository')->with($settingsClass)->willReturn($repository);

        $this->registry->expects($this->once())->method('getManager')->willReturn($manager);

        $this->assertInstanceOf($settingsClass, $service->getSettings($app));
    }

    public function testGetSettingsNoFallback()
    {
        $service = $this->getService();

        $slug = $this->getAlphaNumeric();

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getAppTypeSlug')->willReturn($slug);

        $settingsClass = '\stdClass';

        $appType = $this->getMockBuilder(BaseAppType::class)->disableOriginalConstructor()->getMock();
        $appType->expects($this->once())->method('getSettingsEntityClass')->willReturn($settingsClass);

        $this->applicationTypeBuilder->expects($this->once())->method('getType')->with($slug)->willReturn($appType);

        $settings = false;

        $repository = $this->getMockBuilder(ObjectRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects($this->once())->method('findOneBy')->with(['application' => $app])->willReturn($settings);

        $manager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $manager->expects($this->once())->method('getRepository')->with($settingsClass)->willReturn($repository);

        $this->registry->expects($this->once())->method('getManager')->willReturn($manager);

        $this->assertFalse($service->getSettings($app, false));
    }

    public function testSaveSettings()
    {
        $service = $this->getService();

        $settings = new \stdClass();

        $manager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $manager->expects($this->once())->method('persist')->with($settings);
        $manager->expects($this->once())->method('flush');

        $this->registry->expects($this->exactly(2))->method('getManager')->willReturn($manager);

        $service->saveSettings($settings);
    }

    protected function getService()
    {
        return new AppTypeSettingsService($this->registry, $this->applicationTypeBuilder);
    }

}
