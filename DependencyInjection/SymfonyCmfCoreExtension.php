<?php

namespace Symfony\Cmf\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class SymfonyCmfCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter($this->getAlias() . '.content_basepath', $config['content_basepath']);
        $container->setParameter($this->getAlias() . '.filestore_basepath', $config['filestore_basepath']);
        $container->setParameter($this->getAlias() . '.filestore_relative_basepath', $config['filestore_relative_basepath']);
        $container->setParameter($this->getAlias().'.role', $config['role']);
    }
}
