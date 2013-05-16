<?php

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for custom publish workflow checkers
 */
interface PublishWorkflowCheckerInterface
{
    /**
     * @param $contentDocument content document instance
     * @param bool $ignoreRole if to ignore the role when deciding if to consider the document as published
     * @return boolean
     */
    public function checkIsPublished($contentDocument, $ignoreRole = false);
}
