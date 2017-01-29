<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\PublishWorkflow;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\SecurityContextInterface;

class PublishWorkflowTest extends BaseTestCase
{
    /**
     * @var SecurityContextInterface
     */
    private $publishWorkflowChecker;

    public function setUp()
    {
        $this->publishWorkflowChecker = $this->getContainer()->get('cmf_core.publish_workflow.checker');
    }

    public function testPublishable()
    {
        $doc = $this->createMock(PublishableReadInterface::class);
        $doc->expects($this->any())
            ->method('isPublishable')
            ->will($this->returnValue(true))
        ;

        $this->assertTrue($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $doc));
        $this->assertTrue($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $doc));
    }

    public function testPublishPeriod()
    {
        $doc = $this->createMock(PublishModel::class);
        $doc->expects($this->any())
            ->method('isPublishable')
            ->will($this->returnValue(true))
        ;
        $doc->expects($this->any())
            ->method('getPublishEndDate')
            ->will($this->returnValue(new \DateTime('01/01/1980')))
        ;

        $this->assertFalse($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $doc));
        $this->assertFalse($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $doc));
    }

    public function testIgnoreRoleHas()
    {
        $doc = $this->createMock(PublishModel::class);
        $doc->expects($this->any())
            ->method('isPublishable')
            ->will($this->returnValue(false))
        ;
        $roles = [
            new Role('ROLE_CAN_VIEW_NON_PUBLISHED'),
        ];
        $token = new UsernamePasswordToken('test', 'pass', 'testprovider', $roles);
        $tokenStorage = $this->getContainer()->get('security.token_storage');
        $tokenStorage->setToken($token);

        $this->assertTrue($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $doc));
        $this->assertFalse($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $doc));
    }

    public function testIgnoreRoleNotHas()
    {
        $doc = $this->createMock(PublishModel::class);
        $doc->expects($this->any())
            ->method('isPublishable')
            ->will($this->returnValue(false))
        ;
        $roles = [
            new Role('OTHER_ROLE'),
        ];
        $token = new UsernamePasswordToken('test', 'pass', 'testprovider', $roles);
        $tokenStorage = $this->getContainer()->get('security.token_storage');
        $tokenStorage->setToken($token);

        $this->assertFalse($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $doc));
        $this->assertFalse($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $doc));
    }
}

abstract class PublishModel implements PublishableReadInterface, PublishTimePeriodReadInterface
{
}
