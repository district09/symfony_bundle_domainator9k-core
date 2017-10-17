<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\DependencyInjection\Compiler;

use DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler\ApplicationTypePass;
use DigipolisGent\Domainator9k\CoreBundle\Entity\BaseAppType;
use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use Symfony\Bundle\WebProfilerBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Description of ProvisionCommandTest
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

    public function testTaggedServices() {
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $appTypeBuilder = $this->getMockBuilder(ApplicationTypeBuilder::class)->getMock();
        $container
            ->expects($this->once())
            ->method('has')
            ->with('digip_deploy.application_type_builder')
            ->willReturn(true);

        // Assert no other methods are called.
        $container
            ->expects($this->at(0))
            ->method('findDefinition')
            ->with('digip_deploy.application_type_builder')
            ->willReturn($appTypeBuilder);

        $types = array();
        for ($i = 0; $i<= mt_rand(5, 10); $i++) {
            do {
                $id = (string) mt_rand(0, 100000);
            } while (isset($types[$id]));

            $type = $this->getMockBuilder(BaseAppType::class)->getMockForAbstractClass();
            $type
                ->expects($this->once())
                ->method('parseYamlConfig')
                ->with($this->callback(function(Reference $ref) use ($id) {
                    return ((string) $ref) === $id;
                }));
            $type
                ->expects($this->once())
                ->method('setAppTypeSettingsService')
                ->with($this->callback(function(Reference $ref) {
                    return ((string) $ref) === 'digip_deploy.application_type_settings_service';
                }));
            $type
                ->expects($this->once())
                ->method('setEnvironmentService')
                ->with($this->callback(function(Reference $ref) {
                    return ((string) $ref ) === 'digip_deploy.environment_service';
                }));

            $container
                ->expects($this->at($i+1))
                ->method('findDefinition')
                ->with($id)
                ->willReturn($type);

            $appTypeBuilder
                ->expects($this->at($i))
                ->method('addType')
                ->with($this->callback(function(Reference $ref) use ($id) {
                    return ((string) $ref) === $id;
                }));

            $types[$id] = $type;
        }

        $container
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('digip_deploy.type')
            ->willReturn(array_keys($types));

        $this->process($container);
    }

    protected function process(ContainerBuilder $container)
    {
        $pass = new ApplicationTypePass();
        $pass->process($container);
    }
}
