<?php

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

/**
 * Interface to expose editable publishable flag.
 */
interface PublishableWriteInterface extends PublishableInterface
{
    /**
     * Set the boolean flag whether this content is publishable or not.
     *
     * @param boolean $publishable
     */
    public function setPublishable($publishable);
}
