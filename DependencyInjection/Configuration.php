<?php

namespace Symfony\Cmf\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cmf_content');

        $rootNode
            ->children()
                ->scalarNode('document_manager_name')->defaultValue('default')->end()
                ->arrayNode('publish_workflow')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('checker_service')->defaultValue('cmf_core.publish_workflow.checker.default')->end()
                        ->scalarNode('view_non_published_role')->defaultValue('ROLE_CAN_VIEW_NON_PUBLISHED')->end()
                        ->booleanNode('request_listener')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
