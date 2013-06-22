<?php

namespace Symfony\Cmf\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class CmfCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter($this->getAlias() . '.document_manager_name', $config['document_manager_name']);

        if (isset($config['publish_workflow'])) {
            $this->loadPublishWorkflow($config['publish_workflow'], $loader, $container);
        }
    }

    public function loadPublishWorkflow($config, XmlFileLoader $loader, ContainerBuilder $container) {
        $container->setParameter($this->getAlias().'.publish_workflow.view_non_published_role', $config['view_non_published_role']);
        $loader->load('publish_workflow.xml');

        if ($config['request_listener']) {
            if (!class_exists('Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter')) {
                throw new InvalidConfigurationException('The "publish_workflow.request_listener" may not be enabled unless "Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter" is available.');
            }
        } else {
            $container->removeDefinition($this->getAlias() . '.publish_workflow.request_listener');
        }
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
