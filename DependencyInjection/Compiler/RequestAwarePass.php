<?php

namespace Symfony\Cmf\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * A compiler pass to find all cmf_request_aware services and add them to the
 * RequestAwareListener
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class RequestAwarePass implements CompilerPassInterface
{
    /**
     * Adds services tagged with cmf_request_aware to the RequestAwareListener
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('cmf_core.listener.request_aware')) {
            return;
        }

        $listener = $container->getDefinition('cmf_core.listener.request_aware');
        $services = $container->findTaggedServiceIds('cmf_request_aware');
        foreach ($services as $id => $attributes) {
            $listener->addMethodCall('addService', array(new Reference($id)));
        }
    }
}
