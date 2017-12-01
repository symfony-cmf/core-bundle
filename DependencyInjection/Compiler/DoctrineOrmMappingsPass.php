<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Forward compatibility class to work with Symfony < 2.3 and/or
 * Doctrine ORM bundle < 1.2.1.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class DoctrineOrmMappingsPass implements CompilerPassInterface
{
    private $driver;
    private $driverPattern;
    private $namespaces;
    private $enabledParameter;
    private $managerParameters;

    /**
     * Usually, you should not need to directly instantiate this class but use
     * one of the factory methods.
     *
     * @param Definition|Reference $driver            The driver to use
     * @param array                $namespaces        List of namespaces this driver should handle
     * @param string[]             $managerParameters Ordered list of container parameters that may
     *                                                provide the name of the manager to register
     *                                                the mappings for. The first non-empty name
     *                                                is used, the others skipped
     * @param bool                 $enabledParameter  if specified, the compiler pass only executes
     *                                                if this parameter exists in the service container
     */
    public function __construct($driver, $namespaces, array $managerParameters, $enabledParameter = false)
    {
        $managerParameters[] = 'doctrine.default_entity_manager';
        $this->driver = $driver;
        $this->namespaces = $namespaces;
        $this->enabledParameter = $enabledParameter;
        $this->driverPattern = 'doctrine.orm.%s_metadata_driver';
        $this->managerParameters = $managerParameters;
    }

    /**
     * Register mappings with the metadata drivers.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$this->enabledParameter
            || !$container->hasParameter($this->enabledParameter)
        ) {
            return;
        }

        $chainDriverDefService = $this->getChainDriverServiceName($container);
        $chainDriverDef = $container->getDefinition($chainDriverDefService);
        foreach ($this->namespaces as $namespace) {
            $chainDriverDef->addMethodCall('addDriver', array($this->driver, $namespace));
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return string
     *
     * @throws ParameterNotFoundException
     */
    protected function getChainDriverServiceName(ContainerBuilder $container)
    {
        foreach ($this->managerParameters as $param) {
            if ($container->hasParameter($param)) {
                $name = $container->getParameter($param);
                if ($name) {
                    return sprintf($this->driverPattern, $name);
                }
            }
        }

        throw new ParameterNotFoundException('None of the managerParameters resulted in a valid name');
    }

    /**
     * Create a mapping with the bundle namespace aware SymfonyFileLocator.
     *
     * @param array       $mappings          Hashmap of directory path to namespace
     * @param string[]    $managerParameters List of parameters that could tell which object manager name
     *                                       your bundle uses. This compiler pass will automatically
     *                                       append the parameter name for the default entity manager
     *                                       to this list
     * @param bool|string $enabledParameter  Service container parameter that must be present to
     *                                       enable the mapping. Set to false to not do any check,
     *                                       optional
     */
    public static function createXmlMappingDriver(array $mappings, array $managerParameters = array(), $enabledParameter = false)
    {
        $arguments = array($mappings, '.orm.xml');
        $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator', $arguments);
        $driver = new Definition('Doctrine\ORM\Mapping\Driver\XmlDriver', array($locator));

        return new self($driver, $mappings, $managerParameters, $enabledParameter);
    }
}
