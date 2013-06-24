<?php

namespace Symfony\Cmf\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds all configured publish workflow voters to the access decision manager.
 *
 * This is about the same as Symfony\Bundle\SecurityBundle\DependencyInjection\Compiler\AddSecurityVotersPass
 *
 * @author David Buchmann <mail@davidbu.ch>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class AddPublishedVotersPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('cmf_core.publish_workflow.access_decision_manager')) {
            return;
        }

        $voters = new \SplPriorityQueue();
        foreach ($container->findTaggedServiceIds('cmf_published_voter') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $voters->insert(new Reference($id), $priority);
        }

        $voters = iterator_to_array($voters);
        ksort($voters);

        $container->getDefinition('cmf_core.publish_workflow.access_decision_manager')->replaceArgument(0, array_values($voters));
    }
}
