<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\PublishWorkflow;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class PublishWorkflowCheckerTest extends \PHPUnit_Framework_TestCase
{
    private $publishWorkflowChecker;
    private $role;
    private $document;
    private $accessDecisionManager;
    private $authorizationChecker;
    private $tokenStorage;

    public function setUp()
    {
        $this->role = 'IS_FOOBAR';
        $this->authorizationChecker = \Mockery::mock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        $this->tokenStorage = \Mockery::mock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $this->document = \Mockery::mock('Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableReadInterface');
        $this->accessDecisionManager = \Mockery::mock('Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface');

        $this->publishWorkflowChecker = new PublishWorkflowChecker(
            $this->tokenStorage,
            $this->authorizationChecker,
            $this->accessDecisionManager,
            $this->role
        );
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testIsGranted()
    {
        $token = new AnonymousToken('', '');
        $this->tokenStorage->shouldReceive('getToken')->andReturn($token);

        $this->authorizationChecker->shouldNotReceive('isGranted');

        $this->accessDecisionManager
            ->shouldReceive('decide')->once()
            ->with($token, array(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE), $this->document)
            ->andReturn(true);

        $this->assertTrue($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $this->document));
    }

    public function testNotHasBypassRole()
    {
        $token = new AnonymousToken('', '');
        $this->tokenStorage->shouldReceive('getToken')->andReturn($token);

        $this->authorizationChecker->shouldReceive('isGranted')->once()->with($this->role)->andReturn(false);

        $this->accessDecisionManager
            ->shouldReceive('decide')->once()
            ->with($token, array(PublishWorkflowChecker::VIEW_ATTRIBUTE), $this->document)
            ->andReturn(true);

        $this->assertTrue($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $this->document));
    }

    public function testHasBypassRole()
    {
        $token = new AnonymousToken('', '');
        $this->tokenStorage->shouldReceive('getToken')->andReturn($token);

        $this->authorizationChecker->shouldReceive('isGranted')->once()->with($this->role)->andReturn(true);

        $this->accessDecisionManager->shouldNotReceive('decide');

        $this->assertTrue($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $this->document));
    }

    public function testNoFirewall()
    {
        $this->tokenStorage->shouldReceive('getToken')->andReturnNull();

        $this->authorizationChecker->shouldNotReceive('isGranted');

        $this->accessDecisionManager
            ->shouldReceive('decide')->once()
            ->with(\Mockery::type('Symfony\Component\Security\Core\Authentication\Token\AnonymousToken'), array(PublishWorkflowChecker::VIEW_ATTRIBUTE), $this->document)
            ->andReturn(true);

        $this->assertTrue($this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $this->document));
    }

    public function testSupportsClass()
    {
        $class = 'Test\Class';
        $this->accessDecisionManager->shouldReceive('supportsClass')->once()->with($class)->andReturn(true);

        $this->assertTrue($this->publishWorkflowChecker->supportsClass($class));
    }
}
