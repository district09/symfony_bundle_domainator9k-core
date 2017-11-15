<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\BaseCiAppTypeSettings;
use DigipolisGent\Domainator9k\CoreBundle\Interfaces\ApplicationTypeInterface;
use DigipolisGent\Domainator9k\CoreBundle\Interfaces\CiTypeInterface;
use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Service\CiAppTypeSettingsService;
use DigipolisGent\Domainator9k\CoreBundle\Service\CiTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of CiAppTypeSettingsServiceTest.
 *
 * @author Jelle Sebreghts
 */
class CiAppTypeSettingsServiceTest extends TestCase
{
    use DataGenerator;

    protected $registry;
    protected $applicationTypeBuilder;
    protected $ciTypeBuilder;

    protected function setUp()
    {
        parent::setUp();
        $this->registry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
        $this->applicationTypeBuilder = $this->getMockBuilder(ApplicationTypeBuilder::class)->disableOriginalConstructor()->getMock();
        $this->ciTypeBuilder = $this->getMockBuilder(CiTypeBuilder::class)->disableOriginalConstructor()->getMock();
    }

    public function testGetSettingsExist()
    {
        $service = $this->getService();

        $appTypeSlug = $this->getAlphaNumeric();
        $ciTypeSlug = $this->getAlphaNumeric();
        $settingsClass = $this->getAlphaNumeric();
        $additionalConfig = $this->getAlphaNumeric();

        $ciType = $this->getMockBuilder(CiTypeInterface::class)->disableOriginalConstructor()->getMock();
        $ciType->expects($this->any())->method('getAppTypeSettingsEntityClass')->willReturn($settingsClass);
        $ciType->expects($this->any())->method('getSlug')->willReturn($ciTypeSlug);
        $ciType->expects($this->once())->method('getAdditionalConfig')->willReturn($additionalConfig);

        $appType = $this->getMockBuilder(ApplicationTypeInterface::class)->disableOriginalConstructor()->getMock();
        $appType->expects($this->any())->method('getSlug')->willReturn($appTypeSlug);

        $settings = $this->getMockBuilder(BaseCiAppTypeSettings::class)->disableOriginalConstructor()->getMock();
        $settings->expects($this->once())->method('setAdditionalConfig')->with($additionalConfig);

        $repository = $this->getMockBuilder(ObjectRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects($this->once())->method('findOneBy')->with(['appTypeSlug' => $appTypeSlug, 'ciTypeSlug' => $ciTypeSlug])->willReturn($settings);

        $manager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $manager->expects($this->once())->method('getRepository')->with($settingsClass)->willReturn($repository);

        $this->registry->expects($this->once())->method('getManager')->willReturn($manager);

        $this->assertEquals($settings, $service->getSettings($ciType, $appType));
    }

    public function testGetSettingsNew()
    {
        $service = $this->getService();

        $appTypeSlug = $this->getAlphaNumeric();
        $ciTypeSlug = $this->getAlphaNumeric();
        $additionalConfig = $this->getAlphaNumeric();

        $ciType = $this->getMockBuilder(CiTypeInterface::class)->disableOriginalConstructor()->getMock();
        $ciType->expects($this->any())->method('getSlug')->willReturn($ciTypeSlug);
        $ciType->expects($this->once())->method('getAdditionalConfig')->willReturn($additionalConfig);

        $appType = $this->getMockBuilder(ApplicationTypeInterface::class)->disableOriginalConstructor()->getMock();
        $appType->expects($this->any())->method('getSlug')->willReturn($appTypeSlug);

        $settings = $this->getMockBuilder(BaseCiAppTypeSettings::class)->setConstructorArgs([$ciTypeSlug, $appTypeSlug])->getMock();
        $ciType->expects($this->any())->method('getAppTypeSettingsEntityClass')->willReturn(get_class($settings));

        $repository = $this->getMockBuilder(ObjectRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects($this->once())->method('findOneBy')->with(['appTypeSlug' => $appTypeSlug, 'ciTypeSlug' => $ciTypeSlug])->willReturn(false);

        $manager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $manager->expects($this->once())->method('getRepository')->with(get_class($settings))->willReturn($repository);

        $this->registry->expects($this->once())->method('getManager')->willReturn($manager);

        $this->assertEquals(get_class($settings), get_class($service->getSettings($ciType, $appType)));
    }

    public function testGetSettingsForAppAlreadySet()
    {
        $service = $this->getService();

        $settings = $this->getMockBuilder(BaseCiAppTypeSettings::class)->disableOriginalConstructor()->getMock();

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->any())->method('getCiAppTypeSettings')->willReturn($settings);

        $this->assertEquals($settings, $service->getSettingsForApp($app));
    }

    public function testGetSettingsForAppNotSetButFoundForApp()
    {
        $service = $this->getService();

        $ciTypeSlug = $this->getAlphaNumeric();
        $appTypeSlug = $this->getAlphaNumeric();
        $appTypeSettingsEntityClass = $this->getAlphaNumeric();
        $appId = uniqid();

        $ciType = $this->getMockBuilder(CiTypeInterface::class)->disableOriginalConstructor()->getMock();
        $ciType->expects($this->once())->method('getAppTypeSettingsEntityClass')->willReturn($appTypeSettingsEntityClass);

        $appType = $this->getMockBuilder(ApplicationTypeInterface::class)->disableOriginalConstructor()->getMock();

        $settings = $this->getMockBuilder(BaseCiAppTypeSettings::class)->disableOriginalConstructor()->getMock();

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getCiAppTypeSettings')->willReturn(false);
        $app->expects($this->once())->method('getCiTypeSlug')->willReturn($ciTypeSlug);
        $app->expects($this->once())->method('getAppTypeSlug')->willReturn($appTypeSlug);
        $app->expects($this->once())->method('getId')->willReturn($appId);

        $ciAppTypeSettings = $this->getMockBuilder(BaseCiAppTypeSettings::class)->disableOriginalConstructor()->getMock();

        $repository = $this->getMockBuilder(ObjectRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects($this->once())->method('findOneBy')->with(['appId' => $appId])->willReturn($ciAppTypeSettings);

        $manager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $manager->expects($this->once())->method('getRepository')->with($appTypeSettingsEntityClass)->willReturn($repository);

        $this->ciTypeBuilder->expects($this->once())->method('getType')->with($ciTypeSlug)->willReturn($ciType);
        $this->applicationTypeBuilder->expects($this->once())->method('getType')->with($appTypeSlug)->willReturn($appType);
        $this->registry->expects($this->once())->method('getManager')->willReturn($manager);

        $this->assertEquals($ciAppTypeSettings, $service->getSettingsForApp($app));
    }

    public function testGetSettingsForAppNotSetNotFoundForApp()
    {
        $service = $this->getService();

        $ciTypeSlug = $this->getAlphaNumeric();
        $appTypeSlug = $this->getAlphaNumeric();
        $appTypeSettingsEntityClass = $this->getAlphaNumeric();
        $appId = uniqid();
        $additionalConfig = $this->getAlphaNumeric();

        $ciAppTypeSettings = $this->getMockBuilder(BaseCiAppTypeSettings::class)->disableOriginalConstructor()->getMock();
        $ciAppTypeSettings->expects($this->once())->method('setAdditionalConfig')->with($additionalConfig);

        $ciType = $this->getMockBuilder(CiTypeInterface::class)->disableOriginalConstructor()->getMock();
        $ciType->expects($this->any())->method('getAppTypeSettingsEntityClass')->willReturn($appTypeSettingsEntityClass);
        $ciType->expects($this->any())->method('getSlug')->willReturn($ciTypeSlug);
        $ciType->expects($this->once())->method('getAdditionalConfig')->willReturn($additionalConfig);

        $appType = $this->getMockBuilder(ApplicationTypeInterface::class)->disableOriginalConstructor()->getMock();
        $appType->expects($this->any())->method('getSlug')->willReturn($appTypeSlug);

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getCiAppTypeSettings')->willReturn(false);
        $app->expects($this->once())->method('getCiTypeSlug')->willReturn($ciTypeSlug);
        $app->expects($this->once())->method('getAppTypeSlug')->willReturn($appTypeSlug);
        $app->expects($this->once())->method('getId')->willReturn($appId);

        $repository = $this->getMockBuilder(ObjectRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects($this->at(0))->method('findOneBy')->with(['appId' => $appId])->willReturn(false);
        $repository->expects($this->at(1))->method('findOneBy')->with(['appTypeSlug' => $appTypeSlug, 'ciTypeSlug' => $ciTypeSlug])->willReturn($ciAppTypeSettings);

        $manager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $manager->expects($this->any())->method('getRepository')->with($appTypeSettingsEntityClass)->willReturn($repository);

        $this->ciTypeBuilder->expects($this->once())->method('getType')->with($ciTypeSlug)->willReturn($ciType);
        $this->applicationTypeBuilder->expects($this->once())->method('getType')->with($appTypeSlug)->willReturn($appType);
        $this->registry->expects($this->any())->method('getManager')->willReturn($manager);

        $this->assertEquals($ciAppTypeSettings, $service->getSettingsForApp($app));
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
        return new CiAppTypeSettingsService($this->registry, $this->applicationTypeBuilder, $this->ciTypeBuilder);
    }
}
