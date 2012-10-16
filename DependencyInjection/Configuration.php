<?php

namespace Symfony\Cmf\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('symfony_cmf_content');

        $rootNode
            ->children()
                ->scalarNode('content_basepath')->defaultValue('/cms/content')->end()
                ->scalarNode('role')->defaultValue('IS_AUTHENTICATED_ANONYMOUSLY')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
