<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Service;

use DigipolisGent\Domainator9k\CoreBundle\Interfaces\CiTypeInterface;
use DigipolisGent\Domainator9k\CoreBundle\Service\CiSettingsService;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of CiAppTypeSettingsServiceTest.
 *
 * @author Jelle Sebreghts
 */
class CiSettingsServiceTest extends TestCase
{
    use DataGenerator;

    protected $registry;

    protected function setUp()
    {
        parent::setUp();
        $this->registry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
    }

    public function testGetSettingsExist()
    {
        $service = $this->getService();

        $settingsClass = $this->getAlphaNumeric();

        $deployType = $this->getMockBuilder(CiTypeInterface::class)->disableOriginalConstructor()->getMock();
        $deployType->expects($this->any())->method('getSettingsEntityClass')->willReturn($settingsClass);

        $settings = new \stdClass();

        $repository = $this->getMockBuilder(ObjectRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects($this->once())->method('findAll')->willReturn([$settings]);

        $manager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $manager->expects($this->once())->method('getRepository')->with($settingsClass)->willReturn($repository);

        $this->registry->expects($this->once())->method('getManager')->willReturn($manager);

        $this->assertEquals($settings, $service->getSettings($deployType));
    }

    public function testGetSettingsNew()
    {
        $service = $this->getService();

        $settingsClass = '\stdClass';

        $deployType = $this->getMockBuilder(CiTypeInterface::class)->disableOriginalConstructor()->getMock();
        $deployType->expects($this->any())->method('getSettingsEntityClass')->willReturn($settingsClass);

        $repository = $this->getMockBuilder(ObjectRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects($this->once())->method('findAll')->willReturn(false);

        $manager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $manager->expects($this->once())->method('getRepository')->with($settingsClass)->willReturn($repository);

        $this->registry->expects($this->once())->method('getManager')->willReturn($manager);

        $this->assertInstanceOf('\stdClass', $service->getSettings($deployType));
    }

    public function testSaveSettings()
    {
        $service = $this->getService();

        $settings = new stdClass();

        $manager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $manager->expects($this->once())->method('persist')->with($settings);
        $manager->expects($this->once())->method('flush');

        $this->registry->expects($this->exactly(2))->method('getManager')->willReturn($manager);

        $service->saveSettings($settings);
    }

    protected function getService()
    {
        return new CiSettingsService($this->registry);
    }
}
