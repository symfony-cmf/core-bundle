<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\Voter;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use function is_subclass_of;

/**
 * Workflow voter for the PublishableReadInterface.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class PublishableVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return $subject instanceof PublishableReadInterface
            && $this->supportsAttribute($attribute);
    }

    /**
     * {@inheritDoc}
     *
     * @param PublishableReadInterface $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $subject->isPublishable();
    }

    public function supportsAttribute(string $attribute): bool
    {
        return
            PublishWorkflowChecker::VIEW_ATTRIBUTE === $attribute
            || PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE === $attribute
        ;
    }

    public function supportsType(string $subjectType): bool
    {
        return is_subclass_of($subjectType, PublishableReadInterface::class);
    }
}
