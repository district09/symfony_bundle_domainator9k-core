<?php
namespace DigipolisGent\Domainator9k\CoreBundle\Tests\DependencyInjection\Compiler;

use DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler\CliFactoryProviderCompilerPass;
use DigipolisGent\Domainator9k\CoreBundle\Provider\CliFactoryProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CliFactoryProviderCompilerPassTest extends TestCase
{
    public function testNoProvider()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $container->expects($this->once())->method('has')->with(CliFactoryProvider::class)->willReturn(false);
        $pass = new CliFactoryProviderCompilerPass();
        $this->assertNull($pass->process($container));
    }

    public function testProvider()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $container->expects($this->once())->method('has')->with(CliFactoryProvider::class)->willReturn(true);

        $id = 'my_service_id';

        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('domainator.clifactory')
            ->willReturn([
                $id => [
                    ['for' => '\stdClass']
                ]
            ]);

        $definition = $this->getMockBuilder(Definition::class)->disableOriginalConstructor()->getMock();
        $definition->expects($this->once())->method('addMethodCall')->with(
            'registerCliFactory',
            $this->callback(function (array $args) use ($id) {
                return ($args[0] instanceof Reference) && (string)$args[0] == $id && $args[1] == '\stdClass';
            })
        );

        $container->expects($this->once())->method('getDefinition')->with(CliFactoryProvider::class)->willReturn($definition);
        $pass = new CliFactoryProviderCompilerPass();
        $pass->process($container);
    }
}
