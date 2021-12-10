<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\PublishWorkflow;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PublishWorkflowTest extends BaseTestCase
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $publishWorkflowChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function setUp(): void
    {
        $this->publishWorkflowChecker = $this->getContainer()->get('cmf_core.publish_workflow.checker');
        $this->tokenStorage = $this->getContainer()->get('test.service_container')->get('security.token_storage');
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
            'ROLE_CAN_VIEW_NON_PUBLISHED',
        ];
        $token = new UsernamePasswordToken('test', 'pass', 'testprovider', $roles);
        $this->tokenStorage->setToken($token);

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
            'OTHER_ROLE',
        ];
        $token = new UsernamePasswordToken('test', 'pass', 'testprovider', $roles);
        $this->tokenStorage->setToken($token);

        $this->assertFalse($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $doc));
        $this->assertFalse($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $doc));
    }
}

abstract class PublishModel implements PublishableReadInterface, PublishTimePeriodReadInterface
{
}
