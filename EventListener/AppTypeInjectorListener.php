<?php

namespace DigipolisGent\Domainator9k\CoreBundle\EventListener;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Description of AppTypeInjectorListener.
 *
 * @author Jelle Sebreghts
 */
class AppTypeInjectorListener
{
    /**
     * @var ApplicationTypeBuilder
     */
    protected $appTypeBuilder;

    /**
     * Creates a neww app type injector listener.
     *
     * @param ApplicationTypeBuilder $builder
     */
    public function __construct(ApplicationTypeBuilder $builder)
    {
        $this->appTypeBuilder = $builder;
    }

    /**
     * Binds to the post load event.
     * 
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Application) {
            $entity->setType($this->appTypeBuilder->getType($entity->getAppTypeSlug()));
        }
    }
}
