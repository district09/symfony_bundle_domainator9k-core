<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\DataFixtures;

use DigipolisGent\Domainator9k\CoreBundle\DataFixtures\ORM\LoadApplicationTypes;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\QuuxApplication;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use PHPUnit\Framework\TestCase;

class LoadApplicationTypesTest extends TestCase
{

    public function testLoad()
    {
        $objectManager = $this->getObjectManagerMock();

        $applicationTypeRepository = $this->getApplicationTypeRepositoryMock('quux_application');

        $objectManager
            ->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo(ApplicationType::class))
            ->willReturn($applicationTypeRepository);

        $metadata = new \stdClass();
        $metadata->subClasses = [
            QuuxApplication::class,
        ];

        $metadataFactory = $this->getMetadataFactoryMock(AbstractApplication::class, $metadata);

        $objectManager
            ->expects($this->at(1))
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);


        $environments = [
            new Environment(),
        ];

        $environmentRepository = $this->getEnvironmentRepositoryMock($environments);

        $objectManager
            ->expects($this->at(2))
            ->method('getRepository')
            ->with($this->equalTo(Environment::class))
            ->willReturn($environmentRepository);


        $applicationTypeEnvironmentRepository = $this->getApplicationTypeEnvironmentRepository(null);

        $objectManager
            ->expects($this->at(3))
            ->method('getRepository')
            ->with($this->equalTo(ApplicationTypeEnvironment::class))
            ->willReturn($applicationTypeEnvironmentRepository);

        $fixture = new LoadApplicationTypes();
        $fixture->load($objectManager);
    }

    private function getObjectManagerMock()
    {
        $mock = $this
            ->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();


        return $mock;
    }

    private function getApplicationTypeRepositoryMock($type)
    {
        $mock = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->at(0))
            ->method('findOneBy')
            ->with(['name' => $type])
            ->willReturn(null);

        return $mock;
    }

    private function getMetadataFactoryMock($className, $metadata)
    {
        $mock = $this
            ->getMockBuilder(ClassMetadataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->at(0))
            ->method('getMetadataFor')
            ->with($this->equalTo($className))
            ->willReturn($metadata);

        return $mock;
    }

    private function getEnvironmentRepositoryMock($environments)
    {
        $mock = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->at(0))
            ->method('findAll')
            ->willReturn($environments);

        return $mock;
    }

    private function getApplicationTypeEnvironmentRepository($applicationTypeEnvironment)
    {
        $mock = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->at(0))
            ->method('findOneBy')
            ->willReturn($applicationTypeEnvironment);

        return $mock;
    }
}
