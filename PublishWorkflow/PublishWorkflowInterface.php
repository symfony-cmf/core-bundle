<?php

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

/**
 * Interface models can implement if they want to support publish workflow checking
 */
interface PublishWorkflowInterface
{
    public function getIsPublished();

    public function setIsPublished($isPublished);

    public function getPublishDate();

    public function setPublishDate(\DateTime $publishDate = null);
}