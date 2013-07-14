<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\PublishWorkflow;

use PHPCR\SessionInterface;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Cmf\Bundle\CoreBundle\Twig\Extension\CmfExtension;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class PublishWorkflowTest extends BaseTestCase
{
    /**
     * @var SecurityContextInterface
     */
    private $pwc;

    /**
     * @var CmfExtension
     */
    private $extension;

    public function setUp()
    {
        $this->pwc = $this->getContainer()->get('cmf_core.publish_workflow.checker');
    }

    public function testPublishable()
    {
        $doc = $this->getMock('Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableInterface');
        $doc->expects($this->any())
            ->method('isPublishable')
            ->will($this->returnValue(true))
        ;

        $this->assertTrue($this->pwc->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $doc));
        $this->assertTrue($this->pwc->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $doc));
    }

    public function testPublishPeriod()
    {
        $doc = $this->getMock('Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\PublishWorkflow\PublishModel');
        $doc->expects($this->any())
            ->method('isPublishable')
            ->will($this->returnValue(true))
        ;
        $doc->expects($this->any())
            ->method('getPublishEndDate')
            ->will($this->returnValue(new \DateTime('01/01/1980')))
        ;

        $this->assertFalse($this->pwc->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $doc));
        $this->assertFalse($this->pwc->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $doc));
    }

    public function testIgnoreRoleHas()
    {
        $doc = $this->getMock('Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\PublishWorkflow\PublishModel');
        $doc->expects($this->any())
            ->method('isPublishable')
            ->will($this->returnValue(false))
        ;
        $roles = array(
            new Role('ROLE_CAN_VIEW_NON_PUBLISHED')
        );
        $token = new UsernamePasswordToken('test', 'pass', 'testprovider', $roles);
        $context = $this->getContainer()->get('security.context');
        $context->setToken($token);

        $this->assertTrue($this->pwc->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $doc));
        $this->assertFalse($this->pwc->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $doc));
    }

    public function testIgnoreRoleNotHas()
    {
        $doc = $this->getMock('Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\PublishWorkflow\PublishModel');
        $doc->expects($this->any())
            ->method('isPublishable')
            ->will($this->returnValue(false))
        ;
        $roles = array(
            new Role('OTHER_ROLE')
        );
        $token = new UsernamePasswordToken('test', 'pass', 'testprovider', $roles);
        /** @var $context SecurityContext */
        $context = $this->getContainer()->get('security.context');
        $context->setToken($token);

        $this->assertFalse($this->pwc->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $doc));
        $this->assertFalse($this->pwc->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $doc));
    }
}

abstract class PublishModel implements PublishableInterface, PublishTimePeriodInterface {}