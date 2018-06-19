<?php


namespace DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler;

use DigipolisGent\Domainator9k\CoreBundle\Provider\CacheClearProvider;
use DigipolisGent\Domainator9k\CoreBundle\Provider\CliFactoryProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CliFactoryProviderCompilerPass implements CompilerPassInterface
{

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(CliFactoryProvider::class)) {
            return;
        }
        $definition = $container->getDefinition(CliFactoryProvider::class);

        $taggedServices = $container->findTaggedServiceIds('domainator.clifactory');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('registerCliFactory', array(new Reference($id), $attributes['for']));
            }
        }
    }
}
