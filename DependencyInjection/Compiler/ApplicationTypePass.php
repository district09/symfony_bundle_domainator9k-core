<?php

namespace DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ApplicationTypePass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has('digip_deploy.application_type_builder')) {
            return;
        }

        $definition = $container->findDefinition('digip_deploy.application_type_builder');

        $taggedServices = $container->findTaggedServiceIds('digip_deploy.type');

        foreach ($taggedServices as $id => $tags) {
            $typeService = $container->findDefinition($id);
            $typeService->addMethodCall('parseYamlConfig', array(new Reference($id)));
            $typeService->addMethodCall('setAppTypeSettingsService', [new Reference('digip_deploy.application_type_settings_service')]);
            $typeService->addMethodCall('setEnvironmentService', [new Reference('digip_deploy.environment_service')]);

            // add the transport service to the ChainTransport service
            $definition->addMethodCall('addType', array(new Reference($id)));
        }
    }
}
