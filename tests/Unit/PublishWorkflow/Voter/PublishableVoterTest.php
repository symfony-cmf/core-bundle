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
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\Voter\PublishableVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use function is_subclass_of;

class PublishableVoterTest extends TestCase
{
    private PublishableVoter $voter;

    private TokenInterface&MockObject $token;

    public function setUp(): void
    {
        $this->voter = new PublishableVoter();
        $this->token = $this->createMock(TokenInterface::class);
    }

    public function providePublishWorkflowChecker(): array
    {
        return [
            [
                'expected' => VoterInterface::ACCESS_GRANTED,
                'isPublishable' => true,
                'attributes' => PublishWorkflowChecker::VIEW_ATTRIBUTE,
            ],
            [
                'expected' => VoterInterface::ACCESS_DENIED,
                'isPublishable' => false,
                'attributes' => PublishWorkflowChecker::VIEW_ATTRIBUTE,
            ],
            [
                'expected' => VoterInterface::ACCESS_GRANTED,
                'isPublishable' => true,
                'attributes' => [
                    PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE,
                    PublishWorkflowChecker::VIEW_ATTRIBUTE,
                ],
            ],
            [
                'expected' => VoterInterface::ACCESS_DENIED,
                'isPublishable' => false,
                'attributes' => PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE,
            ],
            [
                'expected' => VoterInterface::ACCESS_ABSTAIN,
                'isPublishable' => true,
                'attributes' => 'other',
            ],
            'at least one supported attribute' => [
                'expected' => VoterInterface::ACCESS_GRANTED,
                'isPublishable' => true,
                'attributes' => [PublishWorkflowChecker::VIEW_ATTRIBUTE, 'other'],
            ],
        ];
    }

    /**
     * @dataProvider providePublishWorkflowChecker
     *
     * use for voters!
     */
    public function testPublishWorkflowChecker(int $expected, bool $isPublishable, array|string $attributes): void
    {
        $attributes = (array) $attributes;
        $doc = $this->createMock(PublishableReadInterface::class);
        $doc
            ->method('isPublishable')
            ->willReturn($isPublishable)
        ;

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
        $doc = $this->createMock(PublishableReadInterface::class);
        $this->assertTrue($this->voter->supportsType(\get_class($doc)));
        $this->assertFalse($this->voter->supportsType(static::class));
    }
}
