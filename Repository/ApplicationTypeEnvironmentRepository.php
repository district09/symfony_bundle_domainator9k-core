<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ApplicationTypeEnvironmentRepository extends EntityRepository
{
    public function findAllByApplicationType($type) {
        return $this->createQueryBuilder('ate')
            ->innerJoin('ate.applicationType', 'at', Query\Expr\Join::ON)
            ->andWhere('at.name = :name')
            ->setParameter('name', $type)
            ->getQuery()
            ->execute();
    }
}
