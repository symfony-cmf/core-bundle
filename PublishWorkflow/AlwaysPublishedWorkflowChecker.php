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

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * A placeholder service to provide instead of the normal publish workflow
 * checker in case the publish workflow is deactivated in the configuration.
 *
 * Services should never accept null as publish workflow checker for security
 * reasons. Typos or service renames could otherwise lead to severe security
 * issues.
 *
 * @author David Buchmann <mail@davidbu.ch>
*/
class AlwaysPublishedWorkflowChecker implements SecurityContextInterface
{
    /**
     * @return null always return null
     */
    public function getToken()
    {
        return null;
    }

    /**
     * Ignored
     */
    public function setToken(TokenInterface $token = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted($attributes, $object = null)
    {
        return true;
    }
}
