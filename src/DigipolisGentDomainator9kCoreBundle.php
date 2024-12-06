<?php

namespace DigipolisGent\Domainator9k\CoreBundle;

use DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler\CacheClearProviderCompilerPass;
use DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler\CliFactoryProviderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class DigipolisGentDomainator9kCoreBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new CacheClearProviderCompilerPass());
        $container->addCompilerPass(new CliFactoryProviderCompilerPass());
    }

    #[\Override]
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yml');
    }
}
