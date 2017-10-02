<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cmf_core');

        $rootNode
            ->children()
                ->arrayNode('persistence')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('phpcr')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('basepath')->defaultValue('/cms')->end()
                                ->scalarNode('manager_registry')->defaultValue('doctrine_phpcr')->end()
                                ->scalarNode('manager_name')->defaultNull()->end()
                                ->scalarNode('translation_strategy')->defaultNull()->end()
                            ->end()
                        ->end() // phpcr
                        ->arrayNode('orm')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('manager_name')->defaultNull()->end()
                            ->end()
                        ->end() // orm
                    ->end()
                ->end()
                ->arrayNode('multilang')
                    ->fixXmlConfig('locale')
                    ->children()
                        ->arrayNode('locales')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('publish_workflow')
                    ->addDefaultsIfNotSet()
                    ->treatFalseLike(['enabled' => false])
                    ->treatTrueLike(['enabled' => true])
                    ->treatNullLike(['enabled' => 'auto'])
                    ->beforeNormalization()
                        ->ifArray()
                        ->then(function ($v) {
                            $v['enabled'] = isset($v['enabled']) ? $v['enabled'] : true;

                            return $v;
                        })
                    ->end()
                    ->children()
                        ->enumNode('enabled')
                            ->values([true, false, 'auto'])
                            ->defaultValue('auto')
                        ->end()
                        ->scalarNode('checker_service')->defaultValue('cmf_core.publish_workflow.checker.default')->end()
                        ->scalarNode('view_non_published_role')->defaultValue('ROLE_CAN_VIEW_NON_PUBLISHED')->end()
                        ->enumNode('request_listener')
                             ->values([true, false, 'auto'])
                            ->defaultValue('auto')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
