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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\Voter\PublishTimePeriodVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use function is_subclass_of;

class PublishTimePeriodVoterTest extends TestCase
{
    private PublishTimePeriodVoter $voter;

    private TokenInterface&MockObject $token;

    public function setUp(): void
    {
        $this->voter = new PublishTimePeriodVoter();
        $this->token = $this->createMock(TokenInterface::class);
    }

    public function providePublishWorkflowChecker(): array
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
    public function testPublishWorkflowChecker(int $expected, ?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate, array|string $attributes = PublishWorkflowChecker::VIEW_ATTRIBUTE, \DateTimeInterface|false $currentTime = false): void
    {
        $attributes = (array) $attributes;
        $doc = $this->createMock(PublishTimePeriodReadInterface::class);

        $doc
            ->method('getPublishStartDate')
            ->willReturn($startDate)
        ;

        $doc
            ->method('getPublishEndDate')
            ->willReturn($endDate)
        ;

        if (false !== $currentTime) {
            $this->voter->setCurrentTime($currentTime);
        }

        $this->assertEquals($expected, $this->voter->vote($this->token, $doc, $attributes));
    }

    public function testUnsupportedClass(): void
    {
        $result = $this->voter->vote(
            $this->token,
            $this,
            [PublishWorkflowChecker::VIEW_ATTRIBUTE]
        );
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testNonClassSubject(): void
    {
        $result = $this->voter->vote($this->token, [1, 2, 3], [PublishWorkflowChecker::VIEW_ATTRIBUTE]);
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testCachableVoterSupportsAttributes(): void
    {
        $this->assertTrue($this->voter->supportsAttribute(PublishWorkflowChecker::VIEW_ATTRIBUTE));
        $this->assertTrue($this->voter->supportsAttribute(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE));
        $this->assertFalse($this->voter->supportsAttribute('other'));
    }

    public function testCachableVoterSupportsSubjectType(): void
    {
        $doc = $this->createMock(PublishTimePeriodReadInterface::class);
        $this->assertTrue($this->voter->supportsType(\get_class($doc)));
        $this->assertFalse($this->voter->supportsType(static::class));
    }
}
