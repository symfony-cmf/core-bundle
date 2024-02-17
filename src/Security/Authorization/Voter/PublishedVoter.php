<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Security\Authorization\Voter;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use function is_subclass_of;

/**
 * This is a security voter registered with the Symfony security system that
 * brings the publish workflow into standard Symfony security.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class PublishedVoter extends Voter
{
    /**
     * @var PublishWorkflowChecker
     */
    private $publishWorkflowChecker;

    public function __construct(PublishWorkflowChecker $publishWorkflowChecker)
    {
        $this->publishWorkflowChecker = $publishWorkflowChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute): bool
    {
        return PublishWorkflowChecker::VIEW_ATTRIBUTE === $attribute
            || PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE === $attribute
        ;
    }

    public function supportsType(string $subjectType): bool
    {
        return is_subclass_of($subjectType, PublishableReadInterface::class)
            || is_subclass_of($subjectType, PublishTimePeriodReadInterface::class);
    }

    protected function supports($attribute, $subject)
    {
        return \is_object($subject) && $this->supportsType(\get_class($subject))
            && $this->supportsAttribute($attribute);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->publishWorkflowChecker->isGranted($attribute, $subject);
    }
}
