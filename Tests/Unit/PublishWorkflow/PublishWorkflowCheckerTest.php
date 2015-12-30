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

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class PublishWorkflowCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PublishWorkflowChecker
     */
    private $pwfc;

    /**
     * @var string
     */
    private $role;

    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var SecurityContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sc;

    /**
     * @var PublishableReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $doc;

    /**
     * @var AccessDecisionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adm;

    public function setUp()
    {
        $this->role = 'IS_FOOBAR';
        $this->container = \Mockery::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->sc = \Mockery::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->doc = \Mockery::mock('Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableReadInterface');
        $this->adm = \Mockery::mock('Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface');
        $this->stdClass = new \stdClass();
        $this->pwfc = new PublishWorkflowChecker($this->container, $this->adm, $this->role);

        $this->container->shouldReceive('get')->with('security.context')->andReturn($this->sc);

        // assuming Symfony <2.6
        $this->container->shouldReceive('has')->with('security.context')->andReturn(true);
        $this->container->shouldReceive('has')->with('security.token_storage')->andReturn(false);
        $this->container->shouldReceive('has')->with('security.authorization_checker')->andReturn(false);
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

    /**
     * Calling.
     */
    public function testIsGranted()
    {
        $token = new AnonymousToken('', '');
        $this->sc->shouldReceive('getToken')->andReturn($token);
        $this->sc->shouldNotReceive('isGranted');
        $this->adm
            ->shouldReceive('decide')->once()
            ->with($token, array(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE), $this->doc)
            ->andReturn(true);

        $this->assertTrue($this->pwfc->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $this->doc));
    }

    public function testNotHasBypassRole()
    {
        $token = new AnonymousToken('', '');
        $this->sc->shouldReceive('getToken')->andReturn($token);
        $this->sc->shouldReceive('isGranted')->once()->with($this->role)->andReturn(false);
        $this->adm
            ->shouldReceive('decide')->once()
            ->with($token, array(PublishWorkflowChecker::VIEW_ATTRIBUTE), $this->doc)
            ->andReturn(true);

        $this->assertTrue($this->pwfc->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $this->doc));
    }

    public function testHasBypassRole()
    {
        $token = new AnonymousToken('', '');
        $this->sc->shouldReceive('getToken')->andReturn($token);
        $this->sc->shouldReceive('isGranted')->once()->with($this->role)->andReturn(true);
        $this->adm->shouldNotReceive('decide');

        $this->assertTrue($this->pwfc->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $this->doc));
    }

    public function testNoFirewall()
    {
        $this->sc->shouldReceive('getToken')->andReturnNull();
        $this->sc->shouldNotReceive('isGranted');
        $this->adm
            ->shouldReceive('decide')->once()
            ->with(\Mockery::type('Symfony\Component\Security\Core\Authentication\Token\AnonymousToken'), array(PublishWorkflowChecker::VIEW_ATTRIBUTE), $this->doc)
            ->andReturn(true);

        $this->assertTrue($this->pwfc->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $this->doc));
    }

    public function testNoSecurityContext()
    {
        $container = \Mockery::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->shouldReceive('get')->with('security.context')
            ->andThrow(new ServiceNotFoundException('Service not defined'));
        $container->shouldReceive('has')->andReturn(false);

        $pwfc = new PublishWorkflowChecker($container, $this->adm, $this->role);

        $this->adm->shouldReceive('decide')->once()->andReturn(false);

        $this->assertFalse($pwfc->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $this->doc));
    }

    public function testSetToken()
    {
        $token = new AnonymousToken('x', 'y');
        $this->pwfc->setToken($token);
        $this->assertSame($token, $this->pwfc->getToken());
    }

    public function testTokenStorageAndAuthenticationManager()
    {
        if (!class_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage')) {
            $this->markTestSkipped('This test requires Symfony >2.6');
        }

        $token = new AnonymousToken('x', 'y');
        $ts = \Mockery::mock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInteface');
        $ac = \Mockery::mock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');

        $container = \Mockery::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->shouldReceive('get')->with('security.token_storage')->andReturn($ts);
        $container->shouldReceive('get')->with('security.authorization_checker')->andReturn($ac);
        $container->shouldReceive('has')->with('security.token_storage')->andReturn(true);
        $container->shouldReceive('has')->with('security.authorization_checker')->andReturn(true);

        $ts->shouldReceive('getToken')->andReturn($token);
        $ac->shouldReceive('isGranted')->with($this->role)->andReturn(true);

        $pwfc = new PublishWorkflowChecker($container, $this->adm, $this->role);

        $this->assertTrue($pwfc->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $this->doc));
    }

    public function testSupportsClass()
    {
        $class = 'Test\Class';
        $this->adm->shouldReceive('supportsClass')->once()->with($class)->andReturn(true);

        $this->assertTrue($this->pwfc->supportsClass($class));
    }
}
