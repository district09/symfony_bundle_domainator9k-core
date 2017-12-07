<?php


namespace DigipolisGent\Domainator9k\CoreBundle\EventListener;


use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class EnvironmentEventListener
 * @package DigipolisGent\Domainator9k\CoreBundle\EventListener
 */
class EnvironmentEventListener
{

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof Environment) {
            $applications = $entityManager->getRepository(AbstractApplication::class)->findAll();

            foreach ($applications as $application) {
                $applicationEnvironment = new ApplicationEnvironment();
                $applicationEnvironment->setApplication($application);
                $applicationEnvironment->setEnvironment($entity);

                $entityManager->persist($applicationEnvironment);
                $entityManager->flush();
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof Environment) {
            foreach ($entity->getApplicationEnvironments() as $applicationEnvironment) {
                $entityManager->remove($applicationEnvironment);
                $entityManager->flush();
            }
        }
    }

}