<?php

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\Voter;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableInterface;

/**
 * Workflow voter for the PublishableInterface.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class PublishableVoter implements VoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return PublishWorkflowChecker::VIEW_ATTRIBUTE === $attribute
            || PublishWorkflowChecker::VIEW_PUBLISHED_ATTRIBUTE === $attribute
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return is_subclass_of($class, 'Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableInterface');
    }

    /**
     * {@inheritdoc}
     *
     * @param PublishableInterface $object
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$this->supportsClass(get_class($object))) {
            return self::ACCESS_ABSTAIN;
        }
        foreach($attributes as $attribute) {
            if (! $this->supportsAttribute($attribute)) {
                return self::ACCESS_ABSTAIN;
            }
        }

        if (! $object->isPublishable()) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_GRANTED;
    }
}