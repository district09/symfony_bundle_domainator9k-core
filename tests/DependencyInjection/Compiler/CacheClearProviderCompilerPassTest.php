<?php
namespace DigipolisGent\Domainator9k\CoreBundle\Tests\DependencyInjection\Compiler;

use DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler\CacheClearProviderCompilerPass;
use DigipolisGent\Domainator9k\CoreBundle\Provider\CacheClearProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CacheClearProviderCompilerPassTest extends TestCase
{
    public function testNoProvider()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $container->expects($this->once())->method('has')->with(CacheClearProvider::class)->willReturn(false);
        $pass = new CacheClearProviderCompilerPass();
        $this->assertNull($pass->process($container));
    }

    public function testProvider()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $container->expects($this->once())->method('has')->with(CacheClearProvider::class)->willReturn(true);

        $id = 'my_service_id';

        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('domainator.cacheclearer')
            ->willReturn([
                $id => [
                    ['for' => '\stdClass']
                ]
            ]);

        $definition = $this->getMockBuilder(Definition::class)->disableOriginalConstructor()->getMock();
        $definition->expects($this->once())->method('addMethodCall')->with(
            'registerCacheClearer',
            $this->callback(function (array $args) use ($id) {
                return ($args[0] instanceof Reference) && (string)$args[0] == $id && $args[1] == '\stdClass';
            })
        );

        $container->expects($this->once())->method('getDefinition')->with(CacheClearProvider::class)->willReturn($definition);
        $pass = new CacheClearProviderCompilerPass();
        $pass->process($container);
    }
}
