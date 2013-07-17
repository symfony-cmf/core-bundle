<?php

namespace Symfony\Cmf\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class CmfCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter($this->getAlias() . '.document_manager_name', $config['document_manager_name']);

        if ($config['publish_workflow']['enabled']) {
            $checker = $this->loadPublishWorkflow($config['publish_workflow'], $loader, $container);
        } else {
            $loader->load('no_publish_workflow.xml');
            $checker = 'cmf_core.publish_workflow.checker.always';
        }
        $container->setAlias('cmf_core.publish_workflow.checker', $checker);

        $this->setupFormTypes($container, $loader);
    }

    /**
     * Setup the cmf_core_checkbox_url_label form type if the routing bundle is there
     *
     * @param array $config
     * @param ContainerBuilder $container
     * @param LoaderInterface $loader
     */
    public function setupFormTypes(ContainerBuilder $container, LoaderInterface $loader)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['CmfRoutingBundle'])) {
            $loader->load('form_type.xml');

            // if there is twig, register our form type with twig
            if ($container->hasParameter('twig.form.resources')) {
                $resources = $container->getParameter('twig.form.resources');
                $container->setParameter('twig.form.resources', array_merge($resources, array('CmfCoreBundle:Form:checkbox_url_label_form_type.html.twig')));
            }
        }
    }

    /**
     * @param $config
     * @param XmlFileLoader $loader
     * @param ContainerBuilder $container
     *
     * @return string the name of the workflow checker service to alias
     *
     * @throws InvalidConfigurationException
     */
    private function loadPublishWorkflow($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $container->setParameter($this->getAlias().'.publish_workflow.view_non_published_role', $config['view_non_published_role']);
        $loader->load('publish_workflow.xml');

        if (!$config['request_listener']) {
            $container->removeDefinition($this->getAlias() . '.publish_workflow.request_listener');
        } elseif (!class_exists('Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter')) {
            throw new InvalidConfigurationException('The "publish_workflow.request_listener" may not be enabled unless "Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter" is available.');
        }

        if (!class_exists('Sonata\AdminBundle\Admin\AdminExtension')) {
            $container->removeDefinition($this->getAlias() . '.admin_extension.publish_workflow.publishable');
            $container->removeDefinition($this->getAlias() . '.admin_extension.publish_workflow.time_period');
        }

        return $config['checker_service'];
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://cmf.symfony.com/schema/dic/core';
    }
}
