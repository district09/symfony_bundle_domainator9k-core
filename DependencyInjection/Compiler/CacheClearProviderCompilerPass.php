<?php

namespace DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler;

use DigipolisGent\Domainator9k\CoreBundle\Provider\CacheClearProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CacheClearProviderCompilerPass implements CompilerPassInterface
{

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(CacheClearProvider::class)) {
            return;
        }
        $definition = $container->getDefinition(CacheClearProvider::class);

        $taggedServices = $container->findTaggedServiceIds('domainator.cacheclearer');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('registerCacheClearer', array(new Reference($id), $attributes['for']));
            }
        }
    }
}
