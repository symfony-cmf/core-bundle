<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\PublishWorkflow;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Component\Security\Core\SecurityContextInterface;

class PublishWorkflowCheckerTest extends \PHPUnit_Framework_Testcase
{
    public function setUp()
    {
        $this->role = 'IS_FOOBAR';
        $this->sc = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->doc = $this->getMock('Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowInterface');
        $this->stdClass = new \stdClass;

        $this->pwfc = new PublishWorkflowChecker($this->role, $this->sc);
    }

    public function testDocDoesntImplementInterface()
    {
        $res = $this->pwfc->checkIsPublished($this->stdClass);
        $this->assertTrue($res);
    }

    public function providePublishWorkflowChecker()
    {
        return array(
            array(array(
                'expected' => true, 
                'granted_role' => 'IS_FOOBAR', 
                'is_publishable' => false, 
            )),
            array(array(
                'expected' => true, 
                'is_publishable' => true, 
            )),
            array(array(
                'expected' => true,
                'granted_role' => 'TEST-3',
                'start_date' => new \DateTime('2000-01-01'),
                'end_date' => new \DateTime('2030-01-01'), 
                'is_publishable' => true, 
            )),
            array(array(
                'expected' => false,
                'granted_role' => 'UNAUTH_ROLE',
                'start_date' => new \DateTime('01/01/2000'),
                'end_date' => new \DateTime('01/01/2001'), 
                'is_publishable' => true, 
            )),
            array(array(
                'expected' => false,
                'granted_role' => 'UNAUTH_ROLE',
                'start_date' => new \DateTime('01/01/2000'),
                'end_date' => new \DateTime('01/01/2030'), 
                'is_publishable' => false, 
            )),
            array(array(
                'expected' => true,
                'granted_role' => 'UNAUTH_ROLE',
                'start_date' => new \DateTime('01/01/2000'), 
                'end_date' => null,  
                'is_publishable' => true, 
            )),
            array(array(
                'expected' => false,
                'granted_role' => 'UNAUTH_ROLE',
                'start_date' => null, 
                'end_date' => new \DateTime('01/01/2000'), 
                'is_publishable' => true, 
            )),
            array(array(
                'expected' => true,
                'granted_role' => 'UNAUTH_ROLE',
                'start_date' => null, 
                'end_date' => new \DateTime('01/01/2030'), 
                'is_publishable' => true, 
            )),
            array(array(
                'expected' => true,
                'granted_role' => 'UNAUTH_ROLE',
                'start_date' => null,
                'end_date' => null, 
                'is_publishable' => null, 
            )),
            array(array(
                'expected' => true,
                'granted_role' => 'TEST-3',
                'start_date' => new \DateTime('2000-01-01'),
                'end_date' => new \DateTime('2030-01-01'), 
                'is_publishable' => null, 
            )),
            array(array(
                'expected' => false,
                'granted_role' => 'UNAUTH_ROLE',
                'start_date' => new \DateTime('01/01/2000'),
                'end_date' => new \DateTime('01/01/2001'), 
                'is_publishable' => null, 
            )),
            array(array(
                'expected' => false,
                'granted_role' => 'UNAUTH_ROLE',
                'start_date' => new \DateTime('01/01/2000'),
                'end_date' => new \DateTime('01/01/2030'), 
                'is_publishable' => false, 
            )),
            array(array(
                'expected' => true,
                'granted_role' => 'UNAUTH_ROLE',
                'start_date' => new \DateTime('01/01/2000'), 
                'end_date' => null,  
                'is_publishable' => null, 
            )),
            array(array(
                'expected' => false,
                'granted_role' => 'UNAUTH_ROLE',
                'start_date' => null, 
                'end_date' => new \DateTime('01/01/2000'), 
                'is_publishable' => null, 
            )),
            array(array(
                'expected' => true,
                'granted_role' => 'UNAUTH_ROLE',
                'start_date' => null, 
                'end_date' => new \DateTime('01/01/2030'), 
                'is_publishable' => null, 
            )),
            // Test overwrite current time
            array(array(
                'expected' => false, 
                'is_publishable' => true, 
                'end_date' => new \DateTime('01/01/2000'), 
                'current_time' => new \DateTime('01/01/2001'),
            )),
            array(array(
                'expected' => true, 
                'is_publishable' => true, 
                'end_date' => new \DateTime('01/01/2000'), 
                'current_time' => new \DateTime('01/01/1980'),
            )),
        );
    }

    /**
     * @dataProvider providePublishWorkflowChecker
     */
    public function testPublishWorkflowChecker($options)
    {
        $options = array_merge(array(
            'expected' => false,
            'granted_role' => 'NONE',
            'start_date' => null,
            'end_date' => null,
            'is_publishable' => null,
            'current_time' => null,
        ), $options);

        $this->sc->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function ($given) use ($options) {
                return $given === $options['granted_role'];
            }));

        $this->doc->expects($this->any())
            ->method('getPublishStartDate')
            ->will($this->returnValue($options['start_date']));

        $this->doc->expects($this->any())
            ->method('getPublishEndDate')
            ->will($this->returnValue($options['end_date']));

        $this->doc->expects($this->any())
            ->method('isPublishable')
            ->will($this->returnValue($options['is_publishable']));

        if ($options['current_time']) {
            $this->pwfc->setCurrentTime($options['current_time']);
        }

        $res = $this->pwfc->checkIsPublished($this->doc);

        if (true === $options['expected']) {
            $this->assertTrue($res);
        } else {
            $this->assertFalse($res);
        }
    }
}
