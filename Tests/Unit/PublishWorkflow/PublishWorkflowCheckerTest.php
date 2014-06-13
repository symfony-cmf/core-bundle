<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
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
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->setMockClassName('Container')
            ->getMock();
        $this->sc = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->container
            ->expects($this->any())
            ->method('get')
            ->with('security.context')
            ->will($this->returnValue($this->sc))
        ;
        $this->container
            ->expects($this->any())
            ->method('has')
            ->with('security.context')
            ->will($this->returnValue(true))
        ;
        $this->doc = $this->getMock('Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableReadInterface');
        $this->adm = $this->getMock('Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface');
        $this->stdClass = new \stdClass;

        $this->pwfc = new PublishWorkflowChecker($this->container, $this->adm, $this->role);
    }

    /**
     * Calling
     */
    public function testIsGranted()
    {
        $token = new AnonymousToken('', '');
        $this->sc->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token))
        ;
        $this->sc->expects($this->never())
            ->method('isGranted')
        ;
        $this->adm->expects($this->once())
            ->method('decide')
            ->with($token, array(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE), $this->doc)
            ->will($this->returnValue(true))
        ;

        $this->assertTrue($this->pwfc->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $this->doc));
    }

    public function testNotHasBypassRole()
    {
        $token = new AnonymousToken('', '');
        $this->sc->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token))
        ;
        $this->sc->expects($this->once())
            ->method('isGranted')
            ->with($this->role)
            ->will($this->returnValue(false))
        ;
        $this->adm->expects($this->once())
            ->method('decide')
            ->with($token, array(PublishWorkflowChecker::VIEW_ATTRIBUTE), $this->doc)
            ->will($this->returnValue(true))
        ;

        $this->assertTrue($this->pwfc->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $this->doc));
    }

    public function testHasBypassRole()
    {
        $token = new AnonymousToken('', '');
        $this->sc->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token))
        ;
        $this->sc->expects($this->once())
            ->method('isGranted')
            ->with($this->role)
            ->will($this->returnValue(true))
        ;
        $this->adm->expects($this->never())
            ->method('decide')
        ;

        $this->assertTrue($this->pwfc->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $this->doc));
    }

    public function testNoFirewall()
    {
        $token = new AnonymousToken('', '');
        $this->sc->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue(null))
        ;
        $this->sc->expects($this->never())
            ->method('isGranted')
        ;
        $this->adm->expects($this->once())
            ->method('decide')
            ->with($token, array(PublishWorkflowChecker::VIEW_ATTRIBUTE), $this->doc)
            ->will($this->returnValue(true))
        ;

        $this->assertTrue($this->pwfc->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $this->doc));
    }

    public function testNoSecurityContext()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->any())
            ->method('get')
            ->with('security.context')
            ->will($this->throwException(new ServiceNotFoundException('Service not defined')))
        ;
        $container
            ->expects($this->any())
            ->method('has')
            ->with('security.context')
            ->will($this->returnValue(false))
        ;
        $this->pwfc = new PublishWorkflowChecker($container, $this->adm, $this->role);

        $this->adm->expects($this->once())
            ->method('decide')
            ->will($this->returnValue(false))
        ;

        $this->assertFalse($this->pwfc->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $this->doc));
    }

    public function testSetToken()
    {
        $token = new AnonymousToken('x', 'y');
        $this->pwfc->setToken($token);
        $this->assertSame($token, $this->pwfc->getToken());
    }

    public function testSupportsClass()
    {
        $class = 'Test\Class';
        $this->adm->expects($this->once())
            ->method('supportsClass')
            ->with($class)
            ->will($this->returnValue(true))
        ;
        $this->assertTrue($this->pwfc->supportsClass($class));
    }
}
