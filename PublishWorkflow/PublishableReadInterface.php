<?php

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

/**
 * Interface for a binary publishable flag.
 *
 * If the flag is false, the content is not published, if it is true it is
 * published if no other voter objects.
 */
interface PublishableReadInterface
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
