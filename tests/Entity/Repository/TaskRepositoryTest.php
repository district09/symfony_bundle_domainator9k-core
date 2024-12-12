<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Repository;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Repository\TaskRepository;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class TaskRepositoryTest extends TestCase
{

    public function testGetNextTask()
    {
        $entityManager = $this->getEntityManagerMock();
        $classMetadata = $this->getClassMetadataMock();
        $querybuilderMock = $this->getQueryBuilderMock();

        $this->addMethodToQueryBuilder($querybuilderMock, 'select', $querybuilderMock);
        $this->addMethodToQueryBuilder($querybuilderMock, 'from', $querybuilderMock);
        $this->addMethodToQueryBuilder($querybuilderMock, 'where', $querybuilderMock);
        $this->addMethodToQueryBuilder($querybuilderMock, 'andWhere', $querybuilderMock);
        $this->addMethodToQueryBuilder($querybuilderMock, 'setParameter', $querybuilderMock);
        $this->addMethodToQueryBuilder($querybuilderMock, 'orderBy', $querybuilderMock);
        $this->addMethodToQueryBuilder($querybuilderMock, 'setMaxResults', $querybuilderMock);
        $this->addMethodToQueryBuilder($querybuilderMock, 'getQuery', $this->getQueryMock());

        $entityManager
            ->expects($this->atLeastOnce())
            ->method('createQueryBuilder')
            ->willReturn($querybuilderMock);

        $repository = new TaskRepository($entityManager, $classMetadata);
        $repository->getNextTask(Task::TYPE_BUILD);
    }

    public function testGetLastTaskIdWithReturn()
    {
        $this->getLastTaskId(new Task());
    }

    public function testGetLastTaskIdWithoutReturn()
    {
        $this->getLastTaskId(null);
    }

    private function getLastTaskId($returnValue)
    {
        $entityManager = $this->getEntityManagerMock();
        $classMetadata = $this->getClassMetadataMock();
        $querybuilderMock = $this->getQueryBuilderMock();

        $this->addMethodToQueryBuilder($querybuilderMock, 'select', $querybuilderMock, 0);
        $this->addMethodToQueryBuilder($querybuilderMock, 'from', $querybuilderMock, 1);
        $this->addMethodToQueryBuilder($querybuilderMock, 'leftJoin', $querybuilderMock, 2);
        $this->addMethodToQueryBuilder($querybuilderMock, 'andWhere', $querybuilderMock, 3);
        $this->addMethodToQueryBuilder($querybuilderMock, 'andWhere', $querybuilderMock, 4);
        $this->addMethodToQueryBuilder($querybuilderMock, 'setParameter', $querybuilderMock, 5);
        $this->addMethodToQueryBuilder($querybuilderMock, 'setParameter', $querybuilderMock, 6);
        $this->addMethodToQueryBuilder($querybuilderMock, 'orderBy', $querybuilderMock, 7);
        $this->addMethodToQueryBuilder($querybuilderMock, 'setMaxResults', $querybuilderMock, 8);
        $this->addMethodToQueryBuilder($querybuilderMock, 'getQuery', $this->getQueryMock($returnValue), 9);

        $entityManager
            ->expects($this->atLeastOnce())
            ->method('createQueryBuilder')
            ->willReturn($querybuilderMock);

        $repository = new TaskRepository($entityManager, $classMetadata);
        $repository->getLastTaskId( new ApplicationEnvironment(),Task::TYPE_BUILD);
    }

    private function getEntityManagerMock()
    {
        $mock = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    private function getClassMetadataMock()
    {
        $mock = $this
            ->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    private function getQueryBuilderMock()
    {
        $mock = $this
            ->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    private function addMethodToQueryBuilder($queryBuilder, $method, $returnValue)
    {
        $queryBuilder
            ->expects($this->atLeastOnce())
            ->method($this->equalTo($method))
            ->willReturn($returnValue);
    }

    private function getQueryMock($returnValue = null)
    {
        $mock = $this
            ->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->atLeastOnce())
            ->method('getOneOrNullResult')
            ->willReturn($returnValue);

        return $mock;
    }
}
