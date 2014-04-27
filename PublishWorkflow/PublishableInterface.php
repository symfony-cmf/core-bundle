<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

/**
 * Interface to expose editable publishable flag.
 */
interface PublishableInterface extends PublishableReadInterface
{
    /**
     * Set the boolean flag whether this content is publishable or not.
     *
     * @param boolean $publishable
     */
    public function setPublishable($publishable);
}
