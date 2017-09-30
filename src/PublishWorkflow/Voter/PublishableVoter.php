<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\Voter;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Workflow voter for the PublishableReadInterface.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class PublishableVoter implements VoterInterface
{
    /**
     * {@inheritdoc}
     *
     * @deprecated To be removed when Symfony 2 support is dropped
     */
    public function supportsAttribute($attribute)
    {
        return PublishWorkflowChecker::VIEW_ATTRIBUTE === $attribute
            || PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE === $attribute
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated To be removed when Symfony 2 support is dropped
     */
    public function supportsClass($class)
    {
        return is_subclass_of($class, PublishableReadInterface::class);
    }

    /**
     * {@inheritdoc}
     *
     * @param PublishableReadInterface $subject
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        if (!is_object($subject) || !$this->supportsClass(get_class($subject))) {
            return self::ACCESS_ABSTAIN;
        }

        $decision = self::ACCESS_GRANTED;
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                // there was an unsupported attribute in the request.
                // now we only abstain or deny if we find a supported attribute
                // and the content is not publishable
                $decision = self::ACCESS_ABSTAIN;

                continue;
            }

            if (!$subject->isPublishable()) {
                return self::ACCESS_DENIED;
            }
        }

        return $decision;
    }
}
