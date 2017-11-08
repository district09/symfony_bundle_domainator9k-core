<?php

namespace DigipolisGent\Domainator9k\CoreBundle;

use DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler\ApplicationTypePass;
use DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler\CiTypePass;
use DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler\TaskPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DigipolisGentDomainator9kCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ApplicationTypePass());
        $container->addCompilerPass(new CiTypePass());
        $container->addCompilerPass(new TaskPass());
    }
}
