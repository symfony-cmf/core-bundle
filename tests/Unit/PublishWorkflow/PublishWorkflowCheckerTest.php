<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\PublishWorkflow;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PublishWorkflowCheckerTest extends TestCase
{
    private PublishWorkflowChecker $publishWorkflowChecker;

    private string $role;

    private PublishableReadInterface&MockObject $document;

    private AccessDecisionManagerInterface&MockObject $accessDecisionManager;

    private AuthorizationCheckerInterface&MockObject $authorizationChecker;

    private TokenStorageInterface&MockObject $tokenStorage;

    public function setUp(): void
    {
        $this->role = 'IS_FOOBAR';
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->document = $this->createMock(PublishableReadInterface::class);
        $this->accessDecisionManager = $this->createMock(AccessDecisionManagerInterface::class);

        $this->publishWorkflowChecker = new PublishWorkflowChecker(
            $this->tokenStorage,
            $this->authorizationChecker,
            $this->accessDecisionManager,
            $this->role
        );
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testIsGranted(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $this->tokenStorage->method('getToken')->willReturn($token);

        $this->authorizationChecker->expects($this->never())->method('isGranted');

        $this->accessDecisionManager
            ->expects($this->once())
            ->method('decide')
            ->with($token, [PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE], $this->document)
            ->willReturn(true);

        $this->assertTrue($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $this->document));
    }

    public function testNotHasBypassRole(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $this->tokenStorage->method('getToken')->willReturn($token);

        $this->authorizationChecker->expects($this->once())->method('isGranted')->with($this->role)->willReturn(false);

        $this->accessDecisionManager->expects($this->once())
            ->method('decide')
            ->with($token, [PublishWorkflowChecker::VIEW_ATTRIBUTE], $this->document)
            ->willReturn(true);

        $this->assertTrue($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $this->document));
    }

    public function testHasBypassRole(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $this->tokenStorage->method('getToken')->willReturn($token);

        $this->authorizationChecker->expects($this->once())->method('isGranted')->with($this->role)->willReturn(true);

        $this->accessDecisionManager->expects($this->never())->method('decide');

        $this->assertTrue($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $this->document));
    }

    public function testNoFirewall(): void
    {
        $this->tokenStorage->method('getToken')->willReturn(null);

        $this->authorizationChecker->expects($this->never())->method('isGranted');

        $this->accessDecisionManager->expects($this->once())
            ->method('decide')
            ->willReturn(true);

        $this->assertTrue($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $this->document));
    }
}
