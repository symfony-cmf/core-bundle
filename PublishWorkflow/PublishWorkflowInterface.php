<?php

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

interface PublishWorkflowInterface
{
    public function getIsPublished();

    public function setIsPublished($isPublished);

    public function getPublishDate();

    public function setPublishDate(\DateTime $publishDate = null);
}