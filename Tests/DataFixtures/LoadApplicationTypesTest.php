<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\DataFixtures;

use DigipolisGent\Domainator9k\CoreBundle\DataFixtures\ORM\LoadApplicationTypes;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\QuuxApplication;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use PHPUnit\Framework\TestCase;

class LoadApplicationTypesTest extends TestCase
{

    public function testLoad()
    {
        $objectManager = $this->getEntityManagerMock();

        $applicationTypeRepository = $this->getApplicationTypeRepositoryMock('quux_application');

        $environments = [
            new Environment(),
        ];

        $environmentRepository = $this->getEnvironmentRepositoryMock($environments);

        $applicationTypeEnvironmentRepository = $this->getApplicationTypeEnvironmentRepository(null);

        $objectManager
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->willReturnCallback(function ($class) use ($applicationTypeRepository, $environmentRepository, $applicationTypeEnvironmentRepository) {
                switch($class) {
                    case ApplicationType::class:
                        return $applicationTypeRepository;
                    case Environment::class:
                        return $environmentRepository;
                    case ApplicationTypeEnvironment::class:
                        return $applicationTypeEnvironmentRepository;
                }
            });

        $metadata = new \stdClass();
        $metadata->subClasses = [
            QuuxApplication::class,
        ];

        $metadataFactory = $this->getMetadataFactoryMock(AbstractApplication::class, $metadata);

        $objectManager
            ->expects($this->atLeastOnce())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        $fixture = new LoadApplicationTypes();
        $fixture->load($objectManager);
    }

    private function getEntityManagerMock()
    {
        $mock = $this
            ->getMockBuilder(EntityManagerInterface::class)
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
            ->expects($this->atLeastOnce())
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
            ->expects($this->atLeastOnce())
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
            ->expects($this->atLeastOnce())
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
            ->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->willReturn($applicationTypeEnvironment);

        return $mock;
    }
}
