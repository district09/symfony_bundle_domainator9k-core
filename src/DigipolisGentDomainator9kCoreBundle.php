<?php

namespace DigipolisGent\Domainator9k\CoreBundle;

use DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler\CacheClearProviderCompilerPass;
use DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler\CliFactoryProviderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DigipolisGentDomainator9kCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new CacheClearProviderCompilerPass());
        $container->addCompilerPass(new CliFactoryProviderCompilerPass());
    }
}
