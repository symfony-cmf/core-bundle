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

use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class CmfCoreExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Prepend persistence, multilang and other common configuration to all cmf
     * bundles.
     *
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        // process the configuration of CmfCoreExtension
        $configs = $container->getExtensionConfig($this->getAlias());
        $parameterBag = $container->getParameterBag();
        $configs = $parameterBag->resolveValue($configs);
        $config = $this->processConfiguration(new Configuration(), $configs);

        $extensions = $container->getExtensions();
        if (isset($config['multilang']['locales'], $extensions['cmf_routing'])) {
            $container->prependExtensionConfig('cmf_routing', [
                'dynamic' => ['locales' => $config['multilang']['locales']],
            ]);
        }

        if ($config['persistence']['phpcr']) {
            $bundles = $container->getParameter('kernel.bundles');
            $persistenceConfig = $config['persistence']['phpcr'];

            foreach ($container->getExtensions() as $name => $extension) {
                $prependConfig = [];

                switch ($name) {
                    case 'cmf_block':
                        $prependConfig = [
                            'persistence' => [
                                'phpcr' => [
                                    'enabled' => $persistenceConfig['enabled'],
                                    'block_basepath' => $persistenceConfig['basepath'].'/content',
                                    'manager_name' => $persistenceConfig['manager_name'],
                                ],
                            ],
                        ];

                        break;
                    case 'cmf_content':
                        $prependConfig = [
                            'persistence' => [
                                'phpcr' => [
                                    'enabled' => $persistenceConfig['enabled'],
                                    'content_basepath' => $persistenceConfig['basepath'].'/content',
                                ],
                            ],
                        ];

                        break;
                    case 'cmf_create':
                        $prependConfig = [
                            'persistence' => [
                                'phpcr' => [
                                    'enabled' => $persistenceConfig['enabled'],
                                ],
                            ],
                        ];
                        // if cmf_media is there, it will prepend the image path to its media_basepath
                        // setting.
                        if (!isset($extensions['cmf_media'])) {
                            $prependConfig['persistence']['phpcr']['image'] = [
                                'enabled' => false,
                                'basepath' => $persistenceConfig['basepath'].'/media',
                            ];
                        }

                        break;
                    case 'cmf_media':
                        $prependConfig = [
                            'persistence' => [
                                'phpcr' => [
                                    'enabled' => $persistenceConfig['enabled'],
                                    'media_basepath' => $persistenceConfig['basepath'].'/media',
                                    'manager_name' => $persistenceConfig['manager_name'],
                                ],
                            ],
                        ];

                        break;
                    case 'cmf_menu':
                        $prependConfig = [
                            'persistence' => [
                                'phpcr' => [
                                    'enabled' => $persistenceConfig['enabled'],
                                    'content_basepath' => $persistenceConfig['basepath'].'/content',
                                    'menu_basepath' => $persistenceConfig['basepath'].'/menu',
                                    'manager_name' => $persistenceConfig['manager_name'],
                                ],
                            ],
                        ];

                        break;
                    case 'cmf_routing':
                        $routePaths = [$persistenceConfig['basepath'].'/routes'];
                        $prependConfig = [
                            'dynamic' => [
                                'enabled' => true,
                                'persistence' => [
                                    'phpcr' => [
                                        'enabled' => $persistenceConfig['enabled'],
                                        'route_basepaths' => $routePaths,
                                        'manager_name' => $persistenceConfig['manager_name'],
                                    ],
                                ],
                            ],
                        ];

                        if (isset($bundles['CmfContentBundle'])) {
                            $prependConfig['dynamic']['generic_controller'] = 'cmf_content.controller:indexAction';
                        }

                        break;
                    case 'cmf_routing_auto':
                        $prependConfig = [
                            'persistence' => [
                                'phpcr' => [
                                    'enabled' => $persistenceConfig['enabled'],
                                    'route_basepath' => $persistenceConfig['basepath'].'/routes',
                                ],
                            ],
                        ];

                        break;
                    case 'cmf_search':
                        $prependConfig = [
                            'persistence' => [
                                'phpcr' => [
                                    'enabled' => $persistenceConfig['enabled'],
                                    'search_basepath' => $persistenceConfig['basepath'].'/content',
                                    'manager_name' => $persistenceConfig['manager_name'],
                                    'manager_registry' => $persistenceConfig['manager_registry'],
                                ],
                            ],
                        ];
                        if (!empty($persistenceConfig['translation_strategy'])) {
                            $prependConfig['persistence']['phpcr']['translation_strategy'] = $persistenceConfig['translation_strategy'];
                        }

                        break;
                    case 'cmf_simple_cms':
                        $prependConfig = [
                            'persistence' => [
                                'phpcr' => [
                                    'enabled' => $persistenceConfig['enabled'],
                                    'basepath' => $persistenceConfig['basepath'].'/simple',
                                    'manager_name' => $persistenceConfig['manager_name'],
                                    'manager_registry' => $persistenceConfig['manager_registry'],
                                ],
                            ],
                        ];

                        break;
                    case 'cmf_seo':
                        $prependConfig = [
                            'persistence' => [
                                'phpcr' => [
                                    'enabled' => $persistenceConfig['enabled'],
                                    'content_basepath' => $persistenceConfig['basepath'].'/content',
                                ],
                            ],
                        ];

                        break;
                }

                if ($prependConfig) {
                    $container->prependExtensionConfig($name, $prependConfig);
                }
            }
        }

        if ($config['persistence']['orm']) {
            $bundles = $container->getParameter('kernel.bundles');
            $persistenceConfig = $config['persistence']['orm'];

            foreach ($container->getExtensions() as $name => $extension) {
                $prependConfig = [];

                switch ($name) {
                    case 'cmf_routing':
                        $prependConfig = [
                            'dynamic' => [
                                'enabled' => true,
                                'persistence' => [
                                    'orm' => [
                                        'enabled' => $persistenceConfig['enabled'],
                                        'manager_name' => $persistenceConfig['manager_name'],
                                    ],
                                ],
                            ],
                        ];

                        if (isset($bundles['CmfContentBundle'])) {
                            $prependConfig['dynamic']['generic_controller'] = 'cmf_content.controller:indexAction';
                        }
                        break;

                    case 'cmf_seo':
                        $prependConfig = [
                            'persistence' => [
                                'orm' => [
                                    'enabled' => $persistenceConfig['enabled'],
                                ],
                            ],
                        ];
                        break;
                }

                if ($prependConfig) {
                    $container->prependExtensionConfig($name, $prependConfig);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if ($config['persistence']['phpcr']['enabled']) {
            $container->setParameter($this->getAlias().'.persistence.phpcr.manager_name', $config['persistence']['phpcr']['manager_name']);
            $container->setParameter($this->getAlias().'.persistence.phpcr.basepath', $config['persistence']['phpcr']['basepath']);

            $templatingHelper = $container->getDefinition($this->getAlias().'.templating.helper');
            $templatingHelper->addMethodCall('setDoctrineRegistry', [
                new Reference($config['persistence']['phpcr']['manager_registry']),
                '%cmf_core.persistence.phpcr.manager_name%',
            ]);
        }
        if ($config['publish_workflow']['enabled']) {
            $this->loadPublishWorkflow($config['publish_workflow'], $loader, $container);
        } else {
            $loader->load('no-publish-workflow.xml');
        }

        if (isset($config['multilang'])) {
            $container->setParameter($this->getAlias().'.multilang.locales', $config['multilang']['locales']);
            $loader->load('translatable.xml');
            if (!empty($config['persistence']['phpcr']['translation_strategy'])) {
                $container->setParameter($this->getAlias().'.persistence.phpcr.translation_strategy', $config['persistence']['phpcr']['translation_strategy']);
            } else {
                $container->removeDefinition('cmf_core.persistence.phpcr.translatable_metadata_listener');
            }
        } else {
            $loader->load('translatable-disabled.xml');
        }

        $this->setupFormTypes($container, $loader);
    }

    /**
     * Setup the cmf_core_checkbox_url_label form type if the routing bundle is there.
     *
     * @param ContainerBuilder $container
     * @param LoaderInterface  $loader
     */
    public function setupFormTypes(ContainerBuilder $container, LoaderInterface $loader)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['CmfRoutingBundle'])) {
            $loader->load('form-type.xml');

            // if there is twig, register our form type with twig
            if ($container->hasParameter('twig.form.resources')) {
                $resources = $container->getParameter('twig.form.resources');
                $container->setParameter('twig.form.resources', array_merge($resources, ['CmfCoreBundle:Form:checkbox_url_label_form_type.html.twig']));
            }
        }
    }

    /**
     * Load and configure the publish workflow services.
     *
     * @param $config
     * @param XmlFileLoader    $loader
     * @param ContainerBuilder $container
     *
     * @throws InvalidConfigurationException
     */
    private function loadPublishWorkflow($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $container->setParameter($this->getAlias().'.publish_workflow.view_non_published_role', $config['view_non_published_role']);
        $loader->load('publish-workflow.xml');

        if (!$config['request_listener']) {
            $container->removeDefinition($this->getAlias().'.publish_workflow.request_listener');
        } elseif (!class_exists(DynamicRouter::class)) {
            throw new InvalidConfigurationException(sprintf(
                'The "publish_workflow.request_listener" may not be enabled unless "%s" is available.',
                DynamicRouter::class
            ));
        }

        $container->setAlias('cmf_core.publish_workflow.checker', $config['checker_service']);
    }

    /**
     * {@inheritdoc}
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return 'http://cmf.symfony.com/schema/dic/core';
    }
}
