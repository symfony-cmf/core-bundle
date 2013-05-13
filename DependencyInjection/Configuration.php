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
                ->scalarNode('role')->defaultValue('IS_AUTHENTICATED_ANONYMOUSLY')->end()
                ->booleanNode('publish_workflow_listener')->defaultFalse()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
