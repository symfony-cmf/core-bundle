<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Security\Authorization\Voter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableInterface;

/**
 * Voter for the PublishableInterface
 */
class PublishableVoter implements VoterInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var bool|string Role allowed to bypass security check or false to never
     *      bypass
     */
    private $bypassingRole;

    /**
     * @param ContainerInterface $container to get the security context from.
     *      We cannot inject the security context directly as this would lead
     *      to a circular reference.
     * @param string $bypassingRole A role that is allowed to bypass the
     *      publishable check.
     */
    public function __construct(ContainerInterface $container, $bypassingRole = false)
    {
        $this->container = $container;
        $this->bypassingRole = $bypassingRole;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return $attribute === 'VIEW';
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
        if (!$this->supportsClass(get_class($object))
            || !in_array('VIEW', $attributes)
        ) {
            return self::ACCESS_ABSTAIN;
        }

        $context = $this->container->get('security.context');
        if ($this->bypassingRole && $context->isGranted($this->bypassingRole)) {
            return self::ACCESS_ABSTAIN;
        }

        if (! $object->isPublishable()) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_ABSTAIN;
    }
}