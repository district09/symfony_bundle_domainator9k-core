<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests;

use DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler\ApplicationTypePass;
use DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler\CiTypePass;
use DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler\TaskPass;
use DigipolisGent\Domainator9k\CoreBundle\DigipolisGentDomainator9kCoreBundle;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Description of DigipolisGentDomainator9kCoreBundleTest
 *
 * @author Jelle Sebreghts
 */
class DigipolisGentDomainator9kCoreBundleTest extends TestCase
{
    public function testBuild() {
        $bundle = new DigipolisGentDomainator9kCoreBundle();
        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $container->expects($this->at(0))->method('addCompilerPass')->with($this->isInstanceOf(ApplicationTypePass::class));
        $container->expects($this->at(1))->method('addCompilerPass')->with($this->isInstanceOf(CiTypePass::class));
        $container->expects($this->at(2))->method('addCompilerPass')->with($this->isInstanceOf(TaskPass::class));
        $bundle->build($container);
    }
}
