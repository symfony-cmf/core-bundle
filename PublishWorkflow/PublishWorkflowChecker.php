<?php

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Implementation of a publish workflow checker. It gives "admins" full access,
 * while for other users it checks that both the publish flag is on and the
 * publish date isn't reached if one is set.
 */
class PublishWorkflowChecker implements PublishWorkflowCheckerInterface
{
    /**
     * @var string the role name for the security check
     */
    protected $requiredRole;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @param string $requiredRole the role to check with the securityContext
     *      (if you pass one), defaults to everybody: IS_AUTHENTICATED_ANONYMOUSLY
     * @param \Symfony\Component\Security\Core\SecurityContextInterface|null $securityContext
     *      the security context to use to check for the role. No security
     *      check if this is null
     */
    public function __construct($requiredRole = "IS_AUTHENTICATED_ANONYMOUSLY", SecurityContextInterface $securityContext = null)
    {
        $this->requiredRole = $requiredRole;
        $this->securityContext = $securityContext;
    }

    public function checkIsPublished($contentDocument, Request $request = null)
    {
        if (!($contentDocument instanceOf PublishWorkflowInterface)) {
            return true;
        }

        if ($this->securityContext && $this->securityContext->isGranted($this->requiredRole)) {
            return true;
        }

        $startDate = $contentDocument->getPublishStartDate();
        $endDate = $contentDocument->getPublishEndDate();
        if (null === $startDate && null === $endDate) {
            return true;
        }

        $now = $request ? $request->server->get('REQUEST_TIME') : time();
        if ((null === $startDate || $now >= $startDate->getTimestamp())
            && (null === $endDate || $now < $endDate->getTimestamp())
        ) {
            return true;
        }

        return false;
    }
}
