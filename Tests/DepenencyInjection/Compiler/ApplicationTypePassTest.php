<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\DependencyInjection\Compiler;

use DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler\ApplicationTypePass;
use Symfony\Bundle\WebProfilerBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Description of ProvisionCommandTest.
 *
 * @author Jelle Sebreghts
 */
class ApplicationTypePassTest extends TestCase
{
    public function testNoAppTypeBuilder()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $container
            ->expects($this->once())
            ->method('has')
            ->with('digip_deploy.application_type_builder')
            ->willReturn(false);

        // Assert no other methods are called.
        $container
            ->expects($this->never())
            ->method('findDefinition')
            ->with('digip_deploy.application_type_builder');

        $container
            ->expects($this->never())
            ->method('findTaggedServiceIds')
            ->with('digip_deploy.type');

        $this->process($container);
    }

    public function testTaggedServices()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $appTypeBuilderDefinition = $this->getMockBuilder(Definition::class)->getMock();
        $container
            ->expects($this->at(0))
            ->method('has')
            ->with('digip_deploy.application_type_builder')
            ->willReturn(true);

        // Assert no other methods are called.
        $container
            ->expects($this->at(1))
            ->method('findDefinition')
            ->with('digip_deploy.application_type_builder')
            ->willReturn($appTypeBuilderDefinition);

        $types = array();
        for ($i = 0; $i <= mt_rand(5, 10); ++$i) {
            do {
                $id = (string) mt_rand(0, 100000);
            } while (isset($types[$id]));

            $typeDefinition = $this->getMockBuilder(Definition::class)->getMock();

            $typeDefinition
                ->expects($this->at(0))
                ->method('addMethodCall')
                ->with(
                    'parseYamlConfig', $this->callback(function (array $args) use ($id) {
                        return ((string) $args[0]) === $id;
                    })
            );

            $typeDefinition
                ->expects($this->at(1))
                ->method('addMethodCall')
                ->with(
                    'setAppTypeSettingsService', $this->callback(function (array $args) {
                        return 'digip_deploy.application_type_settings_service' === ((string) $args[0]);
                    })
            );

            $typeDefinition
                ->expects($this->at(2))
                ->method('addMethodCall')
                ->with(
                    'setEnvironmentService', $this->callback(function (array $args) {
                        return 'digip_deploy.environment_service' === ((string) $args[0]);
                    })
            );

            $container
                ->expects($this->at($i + 3))
                ->method('findDefinition')
                ->with($id)
                ->willReturn($typeDefinition);

            $appTypeBuilderDefinition
                ->expects($this->at($i))
                ->method('addMethodCall')
                ->with(
                    'addType', $this->callback(function (array $args) use ($id) {
                        return ((string) $args[0]) === $id;
                    })
            );

            $types[$id] = $typeDefinition;
        }

        $container
            ->expects($this->at(2))
            ->method('findTaggedServiceIds')
            ->with('digip_deploy.type')
            ->willReturn(array_combine(array_keys($types), array_fill(0, count($types), array('digip_deploy.type'))));

        $this->process($container);
    }

    protected function process(ContainerBuilder $container)
    {
        $pass = new ApplicationTypePass();
        $pass->process($container);
    }
}
