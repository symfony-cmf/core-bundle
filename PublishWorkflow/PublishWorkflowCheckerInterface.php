<?php

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for custom publish workflow checkers
 */
interface PublishWorkflowCheckerInterface
{
    public function checkIsPublished($contentDocument, Request $request = null);
}