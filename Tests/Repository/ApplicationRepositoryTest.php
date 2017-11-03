<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Repository;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Repository\ApplicationRepository;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of ApplicationRepositoryTest
 *
 * @author Jelle Sebreghts
 */
class ApplicationRepositoryTest extends TestCase
{

    use DataGenerator;

    /**
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     *
     * @var ClassMetadata
     */
    protected $metadata;

    protected function setUp()
    {
        parent::setUp();
        $this->entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->metadata = new ClassMetadata(Application::class);
    }

    public function testGetApplicationCount()
    {
        $repository = $this->getRepository();

        $result = mt_rand(1, 100);

        $query = $this->getMockBuilder(AbstractQuery::class)->disableOriginalConstructor()->getMock();
        $query->expects($this->once())->method('getSingleScalarResult')->willReturn($result);

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $queryBuilder->expects($this->any())->method('select')->willReturnSelf();
        $queryBuilder->expects($this->any())->method('from')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('getQuery')->willReturn($query);

        $this->entityManager->expects($this->once())->method('createQueryBuilder')->willReturn($queryBuilder);

        $this->assertEquals($result, $repository->getApplicationCount());
    }

    public function testGetLastCreated()
    {
        $repository = $this->getRepository();

        $count = mt_rand(1, 100);

        $result = [$this->getAlphaNumeric(), $this->getAlphaNumeric()];

        $query = $this->getMockBuilder(AbstractQuery::class)->disableOriginalConstructor()->getMock();
        $query->expects($this->once())->method('getResult')->willReturn($result);

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $queryBuilder->expects($this->any())->method('select')->willReturnSelf();
        $queryBuilder->expects($this->any())->method('from')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('orderBy')->with('app.createdAt', 'DESC')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('setMaxResults')->with($count)->willReturnSelf();
        $queryBuilder->expects($this->once())->method('getQuery')->willReturn($query);

        $this->entityManager->expects($this->once())->method('createQueryBuilder')->willReturn($queryBuilder);

        $this->assertEquals($result, $repository->getLastCreated($count));
    }

    protected function getRepository()
    {
        return new ApplicationRepository($this->entityManager, $this->metadata);
    }

}
