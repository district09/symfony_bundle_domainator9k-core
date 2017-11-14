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

    public function __construct(ApplicationTypeBuilder $builder)
    {
        $this->appTypeBuilder = $builder;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Application) {
            $entity->setType($this->appTypeBuilder->getType($entity->getAppTypeSlug()));
        }
    }
}
