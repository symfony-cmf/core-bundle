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

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use function is_subclass_of;

/**
 * Workflow voter for the PublishTimePeriodReadInterface.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class PublishTimePeriodVoter extends Voter
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
     */
    public function setCurrentTime(\DateTime $currentTime)
    {
        $this->currentTime = $currentTime;
    }

    protected function supports($attribute, $subject)
    {
        return $subject instanceof PublishTimePeriodReadInterface
            && $this->supportsAttribute($attribute);
    }

    /**
     * {@inheritDoc}
     *
     * @param PublishTimePeriodReadInterface $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $startDate = $subject->getPublishStartDate();
        $endDate = $subject->getPublishEndDate();

        return (null === $startDate || $this->currentTime >= $startDate)
            && (null === $endDate || $this->currentTime <= $endDate);
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
        return is_subclass_of($subjectType, PublishTimePeriodReadInterface::class);
    }
}
