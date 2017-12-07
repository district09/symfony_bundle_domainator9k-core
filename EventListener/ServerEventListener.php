<?php


namespace DigipolisGent\Domainator9k\CoreBundle\EventListener;


use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationServer;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class ServerEventListener
 * @package DigipolisGent\Domainator9k\CoreBundle\EventListener
 */
class ServerEventListener
{

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof Server) {
            $applications = $entityManager->getRepository(AbstractApplication::class)->findAll();

            foreach ($applications as $application) {
                $applicationServer = new ApplicationServer();
                $applicationServer->setApplication($application);
                $applicationServer->setServer($entity);

                $entityManager->persist($applicationServer);
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

        if ($entity instanceof Server) {
            foreach ($entity->getApplicationServers() as $applicationServer) {
                $entityManager->remove($applicationServer);
                $entityManager->flush();
            }
        }
    }

}