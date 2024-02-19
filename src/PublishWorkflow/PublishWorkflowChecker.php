<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
class PublishWorkflowChecker implements AuthorizationCheckerInterface
{
    /**
     * This attribute means the user is allowed to see this content, either
     * because it is published or because he is granted the bypassingRole.
     */
    public const VIEW_ATTRIBUTE = 'VIEW';

    /**
     * This attribute means the content is available for viewing by anonymous
     * users. This can be used where the role based exception from the
     * publication check is not wanted.
     *
     * The bypass role is handled by the workflow checker, the individual
     * voters should treat VIEW and VIEW_ANONYMOUS the same.
     */
    public const VIEW_ANONYMOUS_ATTRIBUTE = 'VIEW_ANONYMOUS';

    /**
     * @param bool|string $bypassingRole A role that is allowed to bypass the published check if we
     *                                   ask for the VIEW permission. Ignored on VIEW_ANONYMOUS.
     */
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private AuthorizationCheckerInterface $authorizationChecker,
        private AccessDecisionManagerInterface $accessDecisionManager,
        private string|bool $bypassingRole = false)
    {
    }

    public function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        if (self::VIEW_ATTRIBUTE === $attribute
            && null !== $this->tokenStorage->getToken()
            && $this->authorizationChecker->isGranted($this->bypassingRole)
        ) {
            return true;
        }

        $token = $this->tokenStorage->getToken();

        // not logged in, just check with a dummy token
        if (null === $token) {
            $token = new NullToken();
        }

        return $this->accessDecisionManager->decide($token, [$attribute], $subject);
    }
}
