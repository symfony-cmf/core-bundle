<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Security\Authorization\Voter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodInterface;

/**
 * Voter for the PublishTimePeriodInterface
 */
class PublishTimePeriodVoter implements VoterInterface
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
     * @var \DateTime
     */
    protected $currentTime;

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
        return is_subclass_of($class, 'Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodInterface');
    }

    /**
     * {@inheritdoc}
     *
     * @param PublishTimePeriodInterface $object
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
        $startDate = $object->getPublishStartDate();
        $endDate = $object->getPublishEndDate();

        if ((null === $startDate || $this->currentTime >= $startDate) &&
            (null === $endDate || $this->currentTime < $endDate)
        ) {
            return self::ACCESS_ABSTAIN;
        }

        return self::ACCESS_DENIED;
    }
}