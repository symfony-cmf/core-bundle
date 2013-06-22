<?php

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

/**
 * Interface models can implement if they want to support publish checking with
 * a binary flag.
 *
 * Several publish interfaces can be combined. Publish voters will return DENY
 * if the condition is not met and ABSTAIN if it is met, to allow other voters
 * to influence the decision as well.
 */
interface PublishableInterface
{
    /**
     * Whether this content is publishable at all.
     *
     * A false value indicates that the content is not published. True means it
     * is allowed to show this content.
     *
     * @return boolean
     */
    public function isPublishable();
}
