<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\CoreBundle\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;

/**
 * This is a security voter registered with the Symfony security.context that
 * brings the publish workflow into standard Symfony security.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class PublishedVoter implements VoterInterface
{
    /**
     * @var PublishWorkflowChecker
     */
    private $publishWorkflowChecker;

    /**
     * @param PublishWorkflowChecker $publishWorkflowChecker
     */
    public function __construct(PublishWorkflowChecker $publishWorkflowChecker)
    {
        $this->publishWorkflowChecker = $publishWorkflowChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return PublishWorkflowChecker::VIEW_ATTRIBUTE === $attribute
            || PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE === $attribute
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $this->publishWorkflowChecker->supportsClass($class);
    }

    /**
     * {@inheritdoc}
     *
     * @param object $object
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$this->supportsClass(get_class($object))) {
            return self::ACCESS_ABSTAIN;
        }
        foreach ($attributes as $attribute) {
            if (! $this->supportsAttribute($attribute)) {
                return self::ACCESS_ABSTAIN;
            }
        }

        if ($this->publishWorkflowChecker->isGranted($attributes, $object)) {
            return self::ACCESS_GRANTED;
        }

        return self::ACCESS_DENIED;
    }
}
