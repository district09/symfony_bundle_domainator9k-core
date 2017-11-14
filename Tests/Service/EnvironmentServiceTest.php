<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Service\EnvironmentService;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of EnvironmentServiceTests.
 *
 * @author Jelle Sebreghts
 */
class EnvironmentServiceTest extends TestCase
{
    use DataGenerator;

    protected $registry;

    protected function setUp()
    {
        parent::setUp();
        $this->registry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
    }

    public function testGetEnvironments()
    {
        $service = $this->getService();

        $environment = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();

        $repository = $this->getMockBuilder(ObjectRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects($this->once())->method('findAll')->willReturn([$environment]);

        $manager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $manager->expects($this->once())->method('getRepository')->with('DigipCoreBundle:Environment')->willReturn($repository);

        $this->registry->expects($this->once())->method('getManager')->willReturn($manager);

        $this->assertEquals([$environment], $service->getEnvironments());
    }

    public function testGetEnvironmentChoices()
    {
        $service = $this->getService();

        $name = $this->getAlphaNumeric();

        $environment = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $environment->expects($this->once())->method('getName')->willReturn($name);

        $repository = $this->getMockBuilder(ObjectRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects($this->once())->method('findAll')->willReturn([$environment]);

        $manager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $manager->expects($this->once())->method('getRepository')->with('DigipCoreBundle:Environment')->willReturn($repository);

        $this->registry->expects($this->once())->method('getManager')->willReturn($manager);

        $choices = $service->getEnvironmentChoices();
        $this->assertInstanceOf(ArrayCollection::class, $choices);
        $this->assertEquals([$name => $name], $choices->toArray());
    }

    protected function getService()
    {
        return new EnvironmentService($this->registry);
    }
}
