<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ApplicationTypeEnvironmentRepository extends EntityRepository
{
    /**
     * Get all environments of the specified application type.
     *
     * @param $type
     *   The application type name.
     *
     * @return array
     *   The query results.
     */
    public function findAllByApplicationType($type) {
        return $this->createQueryBuilder('ate')
            ->innerJoin('ate.applicationType', 'at')
            ->andWhere('at.name = :name')
            ->setParameter('name', $type)
            ->getQuery()
            ->getResult();
    }
}
