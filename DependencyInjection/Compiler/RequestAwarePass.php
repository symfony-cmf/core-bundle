<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (version_compare(Kernel::VERSION, '2.3', '<')) {
            $this->configureSynchronizer($container);
        } else {
            $this->makeSynchronized($container);
        }
    }

    /**
     * Configure the request synchronizer for symfony 2.2
     *
     * @param ContainerBuilder $container
     */
    private function configureSynchronizer(ContainerBuilder $container)
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

    /**
     * Make the tagged services synchronized for symfony 2.3 and later.
     *
     * @param ContainerBuilder $container
     */
    private function makeSynchronized(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds('cmf_request_aware');
        foreach ($services as $id => $attributes) {
            $definition = $container->getDefinition($id);
            $definition
                ->setSynchronized(true)
                ->addMethodCall('setRequest', array(
                    new Reference('request', ContainerInterface::NULL_ON_INVALID_REFERENCE, false)
                ))
            ;
        }
    }
}
