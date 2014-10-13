<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * The publish workflow decides if a content is allowed to be shown. Contrary
 * to the symfony core security context, this is even possible without a
 * firewall configured for the current route.
 *
 * The access decision manager is configured to be unanimous by default, and
 * provided with all published voters tagged with cmf_published_voter.
 *
 * If the VIEW attribute is used and there is a firewall in place, there is a
 * check if the current user is granted the bypassing role and if so, he can
 * see even unpublished content.
 *
 * If VIEW_ANONYMOUS is used, the publication check is never bypassed.
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
     * The bypass role is handled by the workflow checker, the individual
     * voters should treat VIEW and VIEW_ANONYMOUS the same.
     */
    const VIEW_ANONYMOUS_ATTRIBUTE = 'VIEW_ANONYMOUS';

    /**
     * We cannot inject the security context directly as this would lead to a
     * circular dependency.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var bool|string Role allowed to bypass the published check if the
     *      VIEW attribute is used, or false to never bypass.
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
     * @param ContainerInterface             $container             To get the security context from.
     * @param AccessDecisionManagerInterface $accessDecisionManager Service to do the actual decision.
     * @param boolean|string                 $bypassingRole         A role that is allowed to bypass
     *                                                              the published check if we ask for
     *                                                              the VIEW permission. Ignored on
     *                                                              VIEW_ANONYMOUS.
     */
    public function __construct(ContainerInterface $container, AccessDecisionManagerInterface $accessDecisionManager, $bypassingRole = false)
    {
        $this->container = $container;
        $this->accessDecisionManager = $accessDecisionManager;
        $this->bypassingRole = $bypassingRole;
    }

    /**
     * {@inheritDoc}
     *
     * Defaults to the token from the default security context, but can be
     * overwritten locally.
     */
    public function getToken()
    {
        if (null === $this->token && $this->container->has('security.context')) {
            return $this->container->get('security.context')->getToken();
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

        if ((count($attributes) === 1)
            && self::VIEW_ATTRIBUTE === reset($attributes)
            && $this->container->has('security.context')
            && null !== $this->container->get('security.context')->getToken()
            && $this->container->get('security.context')->isGranted($this->bypassingRole)
        ) {
            return true;
        }

        $token = $this->getToken();

        // not logged in, just check with a dummy token
        if (null === $token) {
            $token = new AnonymousToken('', '');
        }

        return $this->accessDecisionManager->decide($token, $attributes, $object);
    }
}
