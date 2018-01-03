<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests;

use DigipolisGent\Domainator9k\CoreBundle\DigipolisGentDomainator9kCoreBundle;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Description of DigipolisGentDomainator9kCoreBundleTest.
 *
 * @author Jelle Sebreghts
 */
class DigipolisGentDomainator9kCoreBundleTest extends TestCase
{
    public function testBuild()
    {
        $bundle = new DigipolisGentDomainator9kCoreBundle();
        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $bundle->build($container);
    }
}
