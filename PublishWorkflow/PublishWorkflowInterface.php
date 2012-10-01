<?php

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

/**
 * Interface models can implement if they want to support publish workflow checking
 */
interface PublishWorkflowInterface
{
    public function getPublishStartDate();

    public function setPublishStartDate(\DateTime $publishDate = null);

    public function getPublishEndDate();

    public function setPublishEndDate(\DateTime $publishDate = null);
}