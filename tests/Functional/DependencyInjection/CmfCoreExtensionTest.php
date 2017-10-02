<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\DependencyInjection;

use Symfony\Cmf\Bundle\CoreBundle\DependencyInjection\CmfCoreExtension;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\AlwaysPublishedWorkflowChecker;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CmfCoreExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->registerExtension(new CmfCoreExtension());
        $this->container->setParameter('kernel.bundles', ['CmfCoreBundle' => true]);
    }

    public function testPublishWorkflowDisabled()
    {
        $this->container->loadFromExtension('cmf_core', [
            'publish_workflow' => false,
        ]);

        $this->container->setAlias('app.workflow_checker', 'cmf_core.publish_workflow.checker');

        $this->container->compile();

        $this->assertInstanceOf(AlwaysPublishedWorkflowChecker::class, $this->container->get('app.workflow_checker'));
    }
}
