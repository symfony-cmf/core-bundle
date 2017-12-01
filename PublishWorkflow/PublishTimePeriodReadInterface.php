<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

/**
 * Interface for time period based publish checking.
 *
 * Both start and end date are optional, with null being interpreted as always
 * started resp. never ending.
 */
interface PublishTimePeriodReadInterface
{
    /**
     * Return the date from which the content should be published.
     *
     * A NULL value is interpreted as a date in the past, meaning the content
     * is publishable unless publish end date is set and in the past.
     *
     * @return \DateTime|null
     */
    public function getPublishStartDate();

    /**
     * Return the date at which the content should stop being published.
     *
     * A NULL value is interpreted as saying that the document will
     * never end being publishable.
     *
     * @return \DateTime|null
     */
    public function getPublishEndDate();
}
