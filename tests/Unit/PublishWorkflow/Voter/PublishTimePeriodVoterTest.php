<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\PublishWorkflow\Voter;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\Voter\PublishTimePeriodVoter;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class PublishTimePeriodVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PublishTimePeriodVoter
     */
    private $voter;

    /**
     * @var TokenInterface
     */
    private $token;

    public function setUp()
    {
        $this->voter = new PublishTimePeriodVoter();
        $this->token = new AnonymousToken('', '');
    }

    public function providePublishWorkflowChecker()
    {
        return [
            [
                'expected' => VoterInterface::ACCESS_GRANTED,
                'startDate' => new \DateTime('01/01/2000'),
                'endDate' => new \DateTime('01/02/2030'),
            ],
            [
                'expected' => VoterInterface::ACCESS_DENIED,
                'startDate' => new \DateTime('01/01/2000'),
                'endDate' => new \DateTime('01/01/2001'),
            ],
            [
                'expected' => VoterInterface::ACCESS_GRANTED,
                'startDate' => new \DateTime('01/01/2000'),
                'endDate' => null,
            ],
            [
                'expected' => VoterInterface::ACCESS_DENIED,
                'startDate' => new \DateTime('01/01/2030'),
                'endDate' => null,
            ],
            [
                'expected' => VoterInterface::ACCESS_GRANTED,
                'startDate' => null,
                'endDate' => new \DateTime('01/01/2030'),
            ],
            [
                'expected' => VoterInterface::ACCESS_DENIED,
                'startDate' => null,
                'endDate' => new \DateTime('01/01/2000'),
            ],
            [
                'expected' => VoterInterface::ACCESS_GRANTED,
                'startDate' => null,
                'endDate' => null,
            ],
            // unsupported attribute
            [
                'expected' => VoterInterface::ACCESS_ABSTAIN,
                'startDate' => new \DateTime('01/01/2000'),
                'endDate' => new \DateTime('01/01/2030'),
                'attributes' => [PublishWorkflowChecker::VIEW_ATTRIBUTE, 'other'],
            ],
            // Test overwrite current time
            [
                'expected' => VoterInterface::ACCESS_DENIED,
                'startDate' => null,
                'endDate' => new \DateTime('01/01/2030'),
                'attributes' => PublishWorkflowChecker::VIEW_ATTRIBUTE,
                'currentTime' => new \DateTime('02/02/2030'),
            ],
            [
                'expected' => VoterInterface::ACCESS_GRANTED,
                'startDate' => null,
                'endDate' => new \DateTime('01/01/2000'),
                'attributes' => PublishWorkflowChecker::VIEW_ATTRIBUTE,
                'currentTime' => new \DateTime('01/01/1980'),
            ],
        ];
    }

    /**
     * @dataProvider providePublishWorkflowChecker
     */
    public function testPublishWorkflowChecker($expected, $startDate, $endDate, $attributes = PublishWorkflowChecker::VIEW_ATTRIBUTE, $currentTime = false)
    {
        $attributes = (array) $attributes;
        $doc = $this->createMock(PublishTimePeriodReadInterface::class);

        $doc->expects($this->any())
            ->method('getPublishStartDate')
            ->will($this->returnValue($startDate))
        ;

        $doc->expects($this->any())
            ->method('getPublishEndDate')
            ->will($this->returnValue($endDate))
        ;

        if (false !== $currentTime) {
            $this->voter->setCurrentTime($currentTime);
        }

        $this->assertEquals($expected, $this->voter->vote($this->token, $doc, $attributes));
    }

    public function testUnsupportedClass()
    {
        $result = $this->voter->vote(
            $this->token,
            $this,
            [PublishWorkflowChecker::VIEW_ATTRIBUTE]
        );
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testNonClassSubject()
    {
        $result = $this->voter->vote($this->token, [1, 2, 3], [PublishWorkflowChecker::VIEW_ATTRIBUTE]);
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $result);
    }
}
