<?php

namespace DigipolisGent\Domainator9k\CoreBundle\DependencyInjection\Compiler;

use DigipolisGent\Domainator9k\CoreBundle\Task\TaskInterface;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TaskPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // Always check if the primary service is defined first.
        if (!$container->has('digip_deploy.task_factory')) {
            return;
        }

        $definition = $container->findDefinition('digip_deploy.task_factory');

        $taggedServices = $container->findTaggedServiceIds('digip_deploy.task');

        foreach (array_keys($taggedServices) as $id) {
            $task = $container->findDefinition($id);

            $class = $task->getClass();
            if (!is_subclass_of($class, TaskInterface::class)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Class %s does not implement %s.',
                        $class,
                        TaskInterface::class
                    )
                );
            }
            $definition->addMethodCall('addTaskDefinition', [$class]);
        }
    }
}
