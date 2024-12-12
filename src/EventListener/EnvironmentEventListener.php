<?php


namespace DigipolisGent\Domainator9k\CoreBundle\EventListener;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
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
            $applicationTypes = $entityManager->getRepository(ApplicationType::class)->findAll();

            foreach ($applicationTypes as $applicationType) {
                $applicationTypeEnvironment = new ApplicationTypeEnvironment();
                $applicationTypeEnvironment->setApplicationType($applicationType);
                $applicationTypeEnvironment->setEnvironment($entity);

                $entityManager->persist($applicationTypeEnvironment);
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

            foreach ($entity->getApplicationTypeEnvironments() as $applicationTypeEnvironment) {
                $entityManager->remove($applicationTypeEnvironment);
                $entityManager->flush();
            }
        }
    }
}
