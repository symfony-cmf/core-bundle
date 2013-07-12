<?php

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

/**
 * Interface to expose editable publishable flag.
 */
interface PublishableWriteInterface extends PublishableInterface
{
    /**
     * Set the boolean flag if this content should be published or not.
     *
     * @return boolean
     */
    public function setPublishable($publishable);
}
