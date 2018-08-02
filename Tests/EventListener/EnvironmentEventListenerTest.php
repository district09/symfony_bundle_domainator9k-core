<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\EventListener;


use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\EventListener\EnvironmentEventListener;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\Bar;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\QuuxApplication;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;

class EnvironmentEventListenerTest extends TestCase
{

    public function testPostPersistWithWrongEntity()
    {
        $entity = new Bar();
        $entityManager = $this->getEntityManagerMock();

        $args = $this->getLifecycleEventArgsMock($entity, $entityManager);
        $listener = new EnvironmentEventListener();
        $listener->postPersist($args);
    }

    public function testPostPersist()
    {
        $entity = new Environment();
        $entityManager = $this->getEntityManagerMock();

        $applications = new ArrayCollection();
        $applications->add(new QuuxApplication());

        $applicationTypes = new ArrayCollection();
        $applicationTypes->add(new ApplicationType());

        $entityManager
            ->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo(ApplicationType::class))
            ->willReturn(
                $this->getRepositoryMock($applicationTypes)
            );

        $entityManager
            ->expects($this->at(1))
            ->method('persist');


        $entityManager
            ->expects($this->at(2))
            ->method('flush');

        $args = $this->getLifecycleEventArgsMock($entity, $entityManager);
        $listener = new EnvironmentEventListener();
        $listener->postPersist($args);
    }

    public function testPostRemoveWithWrongEntity()
    {
        $entity = new Bar();
        $entityManager = $this->getEntityManagerMock();

        $args = $this->getLifecycleEventArgsMock($entity, $entityManager);
        $listener = new EnvironmentEventListener();
        $listener->postRemove($args);
    }

    public function testPostRemove()
    {
        $entity = new Environment();
        $entity->addApplicationEnvironment(new ApplicationEnvironment());
        $entity->addApplicationTypeEnvironment(new ApplicationTypeEnvironment());
        $entityManager = $this->getEntityManagerMock();

        $entityManager
            ->expects($this->at(0))
            ->method('remove');


        $entityManager
            ->expects($this->at(1))
            ->method('flush');

        $entityManager
            ->expects($this->at(2))
            ->method('remove');


        $entityManager
            ->expects($this->at(3))
            ->method('flush');

        $args = $this->getLifecycleEventArgsMock($entity, $entityManager);
        $listener = new EnvironmentEventListener();
        $listener->postRemove($args);
    }

    private function getRepositoryMock(ArrayCollection $arrayCollection){
        $mock = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->at(0))
            ->method('findAll')
            ->willReturn($arrayCollection);

        return $mock;
    }

    private function getEntityManagerMock()
    {
        $mock = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    private function getLifecycleEventArgsMock($entity, $entityManager)
    {
        $mock = $this
            ->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->at(0))
            ->method('getEntity')
            ->willReturn($entity);

        $mock
            ->expects($this->at(1))
            ->method('getEntityManager')
            ->willReturn($entityManager);

        return $mock;
    }

}