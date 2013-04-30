<?php

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

/**
 * Interface models can implement if they want to support publish workflow checking
 */
interface PublishWorkflowInterface
{
    /**
     * Return the date from which the content should
     * be considered publishable.
     *
     * A NULL value should be interpreted as saying that 
     * the content has always been publishable.
     *
     * @return \DateTime|null
     */
    public function getPublishStartDate();

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
     * Return the date at which the content should
     * stop being published.
     *
     * A NULL value should be interpreted as saying that
     * the document will always be publishable.
     *
     * @return \DateTime|null
     */
    public function getPublishEndDate();

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
     * Return the base publishable state of the content.
     *
     * A false value indicates that the content should, under
     * no circumstances, be published. A true value indicates
     * that the content is publishable - but is subject to
     * the $publishStartDate and $publishEndDate if they are
     * set.
     *
     * @return boolean
     */
    public function isPublished();
}
