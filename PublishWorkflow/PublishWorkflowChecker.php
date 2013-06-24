<?php

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Implementation of a publish workflow checker as a security context.
 *
 * It gives "admins" full access,
 * while for other users it runs all cmf_published_voter
 *
 * @author David Buchmann <mail@davidbu.ch>
*/
class PublishWorkflowChecker implements SecurityContextInterface
{
    /**
     * This attribute means the user is allowed to see this content, either
     * because it is published or because he is granted the bypassingRole.
     */
    const VIEW_ATTRIBUTE = 'VIEW';

    /**
     * This attribute means the content is available for viewing by anonymous
     * users. This can be used where the role based exception from the
     * publication check is not wanted.
     *
     * The role exception is handled by the workflow checker, the individual
     * voters should treat VIEW and VIEW_PUBLISHED the same.
     */
    const VIEW_PUBLISHED_ATTRIBUTE = 'VIEW_PUBLISHED';

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
     * @var AccessDecisionManagerInterface
     */
    private $accessDecisionManager;

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * @param ContainerInterface $container to get the security context from.
     *      We cannot inject the security context directly as this would lead
     *      to a circular reference.
     * @param AccessDecisionManagerInterface $accessDecisionManager
     * @param string $bypassingRole A role that is allowed to bypass the
     *      publishable check.
     */
    public function __construct(ContainerInterface $container, AccessDecisionManagerInterface $accessDecisionManager, $bypassingRole = false)
    {
        $this->container = $container;
        $this->accessDecisionManager = $accessDecisionManager;
        $this->bypassingRole = $bypassingRole;
    }

    /**
     * {@inheritDoc}
     */
    public function getToken()
    {
        if (null === $this->token) {
            $securityContext = $this->container->get('security.context');

            return $securityContext->getToken();
        }

        return $this->token;
    }

    /**
     * {@inheritDoc}
     */
    public function setToken(TokenInterface $token = null)
    {
        $this->token = $token;
    }

    /**
     * Checks if the access decision manager supports the given class.
     *
     * @param string $class A class name
     *
     * @return boolean true if this decision manager can process the class
     */
    public function supportsClass($class)
    {
        return $this->accessDecisionManager->supportsClass($class);
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted($attributes, $object = null)
    {
        if (!is_array($attributes)) {
            $attributes = array($attributes);
        }

        $securityContext = $this->container->get('security.context');
        if (null !== $securityContext->getToken()
            && (count($attributes) === 1)
            && self::VIEW_ATTRIBUTE === reset($attributes)
            && $securityContext->isGranted($this->bypassingRole)
        ) {
            return true;
        }

        $token = $this->getToken();
        if (null === $token) {
            // not logged in, surely we can not skip the check.
            // create a dummy token to check for publication even if no
            // firewall is present.
            $token = new AnonymousToken('', '');
        }

        return $this->accessDecisionManager->decide($token, $attributes, $object);
    }
}