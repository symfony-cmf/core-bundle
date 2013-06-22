<?php

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

/**
 * Interface models can implement to expose editable publishing settings.
 */
interface PublishWorkflowInterface extends PublishableInterface, PublishTimePeriodInterface
{
    /**
     * Set the date from which the content should
     * be considered publishable.
     *
     * Setting a NULL value asserts that the content
     * has always been publishable.
     *
     * @param \DateTime|null $publishDate
     */
    public function setPublishStartDate(\DateTime $publishDate = null);

    /**
     * Set the date at which the content should
     * stop being published.
     *
     * Setting a NULL value asserts that the
     * content will always be publishable.
     *
     * @param \DateTime|null $publishDate
     */
    public function setPublishEndDate(\DateTime $publishDate = null);

    /**
     * Set the boolean flag if this content should be published or not.
     *
     * @return boolean
     */
    public function setPublishable($publishable);
}
