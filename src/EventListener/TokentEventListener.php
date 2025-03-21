<?php


namespace DigipolisGent\Domainator9k\CoreBundle\EventListener;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Token;
use DigipolisGent\Domainator9k\CoreBundle\Twig\TemplateHelpExtension;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Class TokenEventListener
 * @package DigipolisGent\Domainator9k\CoreBundle\EventListener
 */
class TokenEventListener
{

    public function __construct(protected TagAwareCacheInterface $cache)
    {
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->invalidateTemplateCache($args->getObject());
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $this->invalidateTemplateCache($args->getObject());
    }

    protected function invalidateTemplateCache($entity)
    {
        if ($entity instanceof Token) {
            $this->cache->invalidateTags([TemplateHelpExtension::CACHE_TAG]);
        }
    }
}
