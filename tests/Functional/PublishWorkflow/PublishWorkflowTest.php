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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PublishWorkflowTest extends BaseTestCase
{
    private AuthorizationCheckerInterface $publishWorkflowChecker;
    private TokenStorageInterface $tokenStorage;

    public function setUp(): void
    {
        $this->publishWorkflowChecker = self::getContainer()->get('cmf_core.publish_workflow.checker');
        $this->tokenStorage = self::getContainer()->get('test.service_container')->get('security.token_storage');
    }

    public function testPublishable(): void
    {
        $doc = $this->createMock(PublishableReadInterface::class);
        $doc
            ->method('isPublishable')
            ->willReturn(true)
        ;

        $this->assertTrue($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $doc));
        $this->assertTrue($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $doc));
    }

    public function testPublishPeriod(): void
    {
        $doc = $this->createMock(PublishModel::class);
        $doc
            ->method('isPublishable')
            ->willReturn(true)
        ;
        $doc
            ->method('getPublishEndDate')
            ->willReturn(new \DateTime('01/01/1980'))
        ;

        $this->assertFalse($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $doc));
        $this->assertFalse($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $doc));
    }

    public function testIgnoreRoleHas(): void
    {
        $doc = $this->createMock(PublishModel::class);
        $doc
            ->method('isPublishable')
            ->willReturn(false)
        ;
        $roles = [
            'ROLE_CAN_VIEW_NON_PUBLISHED',
        ];
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($this->createMock(UserInterface::class)); // authorization checker will ignore roles if user is null
        $token->method('getRoleNames')->willReturn($roles);
        $this->tokenStorage->setToken($token);

        $this->assertTrue($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $doc));
        $this->assertFalse($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $doc));
    }

    public function testIgnoreRoleNotHas(): void
    {
        $doc = $this->createMock(PublishModel::class);
        $doc
            ->method('isPublishable')
            ->willReturn(false)
        ;
        $roles = [
            'OTHER_ROLE',
        ];
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($this->createMock(UserInterface::class));
        $token->method('getRoleNames')->willReturn($roles);
        $this->tokenStorage->setToken($token);

        $this->assertFalse($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $doc));
        $this->assertFalse($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $doc));
    }
}

abstract class PublishModel implements PublishableReadInterface, PublishTimePeriodReadInterface
{
}
