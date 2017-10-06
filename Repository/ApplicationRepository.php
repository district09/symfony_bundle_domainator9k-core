<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ApplicationRepository extends EntityRepository
{
    public function getApplicationCount()
    {
        return $this->createQueryBuilder('app')
            ->select('COUNT(app.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getLastCreated($count = 5)
    {
        return $this->createQueryBuilder('app')
            ->orderBy('app.createdAt', 'DESC')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult();
    }
}
