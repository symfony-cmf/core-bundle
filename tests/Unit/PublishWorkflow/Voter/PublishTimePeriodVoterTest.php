<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\PublishWorkflow\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\Voter\PublishTimePeriodVoter;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use function is_subclass_of;

class PublishTimePeriodVoterTest extends TestCase
{
    /**
     * @var PublishTimePeriodVoter
     */
    private $voter;

    /**
     * @var TokenInterface
     */
    private $token;

    public function setUp(): void
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
            'at least one supported attribute' => [
                'expected' => VoterInterface::ACCESS_GRANTED,
                'startDate' => new \DateTime('01/01/2000'),
                'endDate' => new \DateTime('01/01/2030'),
                'attributes' => [PublishWorkflowChecker::VIEW_ATTRIBUTE, 'other'],
            ],
            'Test overwrite current time to past end date' => [
                'expected' => VoterInterface::ACCESS_DENIED,
                'startDate' => null,
                'endDate' => new \DateTime('01/01/2030'),
                'attributes' => PublishWorkflowChecker::VIEW_ATTRIBUTE,
                'currentTime' => new \DateTime('02/02/2030'),
            ],
            'Test overwrite current time to before end date' => [
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

    public function testCachableVoterSupportsAttributes()
    {
        if (!$this->voter instanceof CacheableVoterInterface) {
            $this->assertFalse(
                is_subclass_of(Voter::class, CacheableVoterInterface::class),
                'Voter cache is supported and expected to be implemented'
            );
        }

        $this->assertTrue($this->voter->supportsAttribute(PublishWorkflowChecker::VIEW_ATTRIBUTE));
        $this->assertTrue($this->voter->supportsAttribute(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE));
        $this->assertFalse($this->voter->supportsAttribute('other'));
    }

    public function testCachableVoterSupportsSubjectType()
    {
        if (!$this->voter instanceof CacheableVoterInterface) {
            $this->assertFalse(
                is_subclass_of(Voter::class, CacheableVoterInterface::class),
                'Voter cache is supported and expected to be implemented'
            );
        }

        $doc = $this->createMock(PublishTimePeriodReadInterface::class);
        $this->assertTrue($this->voter->supportsType(\get_class($doc)));
        $this->assertFalse($this->voter->supportsType(static::class));
    }
}
