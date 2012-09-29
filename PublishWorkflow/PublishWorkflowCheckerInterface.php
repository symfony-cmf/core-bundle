<?php

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

use Symfony\Component\HttpFoundation\Request;

interface PublishWorkflowCheckerInterface
{
    public function checkIsPublished($contentDocument, Request $request = null);
}