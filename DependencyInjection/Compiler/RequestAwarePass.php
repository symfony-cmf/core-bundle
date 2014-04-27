<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * BC: A compiler pass to find all cmf_request_aware services and adjust the
 * service definition.
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
        $services = $container->findTaggedServiceIds('cmf_request_aware');
        foreach ($services as $id => $attributes) {
            trigger_error("Service $id is using the deprecated tag cmf_request_aware");

            $definition = $container->getDefinition($id);
            $definition
                ->addMethodCall('setRequest', array(
                    new Reference('request', ContainerInterface::NULL_ON_INVALID_REFERENCE, false)
                ))
            ;
        }
    }
}
