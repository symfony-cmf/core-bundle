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

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Workflow voter for the PublishTimePeriodReadInterface.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class PublishTimePeriodVoter implements VoterInterface
{
    /**
     * @var \DateTime
     */
    protected $currentTime;

    public function __construct()
    {
        // we create the timestamp on instantiation to avoid glitches due to
        // the time passing during the request
        $this->currentTime = new \DateTime();
    }

    /**
     * Overwrite the current time.
     *
     * @param \DateTime $currentTime
     */
    public function setCurrentTime(\DateTime $currentTime)
    {
        $this->currentTime = $currentTime;
    }

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
        return is_subclass_of($class, PublishTimePeriodReadInterface::class);
    }

    /**
     * {@inheritdoc}
     *
     * @param PublishTimePeriodReadInterface $subject
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        if (!is_object($subject) || !$this->supportsClass(get_class($subject))) {
            return self::ACCESS_ABSTAIN;
        }

        $startDate = $subject->getPublishStartDate();
        $endDate = $subject->getPublishEndDate();

        $decision = self::ACCESS_GRANTED;
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                // there was an unsupported attribute in the request.
                // now we only abstain or deny if we find a supported attribute
                // and the content is not publishable
                $decision = self::ACCESS_ABSTAIN;
                continue;
            }

            if (
                (null !== $startDate && $this->currentTime < $startDate)
                || (null !== $endDate && $this->currentTime > $endDate)
            ) {
                return self::ACCESS_DENIED;
            }
        }

        return $decision;
    }
}
