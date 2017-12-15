<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Entity\Repository;


use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use Doctrine\ORM\EntityRepository;

class BuildRepository extends EntityRepository
{

    public function getNextBuild()
    {
        return $this->_em->createQueryBuilder()
            ->select('b')
            ->from(Build::class, 'b')
            ->where('b.status=:status')
            ->setParameter('status', Build::STATUS_NEW)
            ->orderBy('b.created')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

}