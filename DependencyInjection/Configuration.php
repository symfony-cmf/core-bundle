<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
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
                                ->enumNode('use_sonata_admin')
                                    ->values(array(true, false, 'auto'))
                                    ->defaultValue('auto')
                                ->end()
                                ->scalarNode('translation_strategy')->defaultNull()->end()
                            ->end()
                        ->end() // phpcr
                        ->arrayNode('orm')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('manager_name')->defaultNull()->end()
                                ->enumNode('use_sonata_admin')
                                    ->values(array(true, false, 'auto'))
                                    ->defaultValue('auto')
                                ->end()
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
                    ->canBeDisabled()
                    ->children()
                        ->scalarNode('checker_service')->defaultValue('cmf_core.publish_workflow.checker.default')->end()
                        ->scalarNode('view_non_published_role')->defaultValue('ROLE_CAN_VIEW_NON_PUBLISHED')->end()
                        ->booleanNode('request_listener')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('sonata_admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('extensions')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('publishable')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('form_group')->defaultValue('form.group_publish_workflow')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('publish_time')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('form_group')->defaultValue('form.group_publish_workflow')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('translatable')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('form_group')->defaultValue('form.group_general')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
