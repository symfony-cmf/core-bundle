<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\PublishWorkflow;

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
            array(
                true,
                'IS_FOOBAR',
            ),
            array(
                true,
                'UNAUTH_ROLE',
                null,
                null,
                true, // is published = true
            ),
            array(
                true,
                'TEST-3',
                new \DateTime('2000-01-01'),
                new \DateTime('2030-01-01'), // in valid date range
                true, // is published = true
            ),
            array(
                false,
                'UNAUTH_ROLE',
                new \DateTime('01/01/2000'),
                new \DateTime('01/01/2001'), // in in-valid date range
                true, // is published = true
            ),
            array(
                false,
                'UNAUTH_ROLE',
                new \DateTime('01/01/2000'),
                new \DateTime('01/01/2030'), // in valid date range
                false, // is published = false
            ),
            array(
                true,
                'UNAUTH_ROLE',
                new \DateTime('01/01/2000'), // already started
                null, // no end date
                true, // is published = true
            ),
            array(
                false,
                'UNAUTH_ROLE',
                null, // no start date
                new \DateTime('01/01/2000'), // already finished
                true, // is published = true
            ),
            array(
                true,
                'UNAUTH_ROLE',
                null, // no start date
                new \DateTime('01/01/2030'), // will finish in 2030
                true, // is published = true
            ),
        );
    }

    /**
     * @dataProvider providePublishWorkflowChecker
     */
    public function testPublishWorkflowChecker(
        $expected = false,
        $grantedRole = 'NONE', 
        $startDate = null, 
        $endDate = null, 
        $isPublished = false
    )
    {
        $this->sc->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function ($given) use ($grantedRole) {
                return $given === $grantedRole;
            }));

        $this->doc->expects($this->any())
            ->method('getPublishStartDate')
            ->will($this->returnValue($startDate));

        $this->doc->expects($this->any())
            ->method('getPublishEndDate')
            ->will($this->returnValue($endDate));

        $this->doc->expects($this->any())
            ->method('isPublished')
            ->will($this->returnValue($isPublished));

        $res = $this->pwfc->checkIsPublished($this->doc);

        if (true === $expected) {
            $this->assertTrue($res);
        } else {
            $this->assertFalse($res);
        }
    }
}
