<?php


namespace DigipolisGent\Domainator9k\CoreBundle\EventListener;


use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationServer;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Event\BuildEvent;
use DigipolisGent\Domainator9k\CoreBundle\Event\DestroyEvent;
use DigipolisGent\Domainator9k\CoreBundle\Service\BuildLoggerService;
use DigipolisGent\Domainator9k\CoreBundle\Tools\StringHelper;
use DigipolisGent\Domainator9k\SockBundle\Service\ApiService;
use DigipolisGent\SettingBundle\Service\DataValueService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class DestroyEventListener
 * @package DigipolisGent\Domainator9k\CoreBundle\EventListener
 */
class DestroyEventListener
{

    private $buildLoggerService;
    private $entityManager;

    /**
     * BuildEventListener constructor.
     * @param BuildLoggerService $buildLoggerService
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(BuildLoggerService $buildLoggerService, EntityManagerInterface $entityManager)
    {
        $this->buildLoggerService = $buildLoggerService;
        $this->entityManager = $entityManager;
    }

    /**
     * @param BuildEvent $event
     */
    public function onStartDestroy(DestroyEvent $event)
    {
        $build = $event->get();
        $build->setStatus(Build::IN_PROGRESS);
        $this->entityManager->persist($build);
        $this->entityManager->flush();
        $this->buildLoggerService->setBuild($event->getBuild());
    }

    /**
     * @param BuildEvent $event
     */
    public function onEndBuild(BuildEvent $event)
    {
        $build = $event->getBuild();
        $build->setStatus(Build::STATUS_PROCESSED);
        $this->entityManager->persist($build);
        $this->entityManager->flush();
    }
}