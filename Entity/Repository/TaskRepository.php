<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Entity\Repository;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use Doctrine\ORM\EntityRepository;

class TaskRepository extends EntityRepository
{

    public function getNextTask($type)
    {
        return $this->_em->createQueryBuilder()
            ->select('b')
            ->from(Task::class, 'b')
            ->where('b.status=:status')
            ->andWhere('b.type=:type')
            ->setParameter('status', Task::STATUS_NEW)
            ->setParameter('type', $type)
            ->orderBy('b.created')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
