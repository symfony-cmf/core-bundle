<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\CoreBundle\Model;

/**
 * An interface for models with a parent document.
 */
interface ChildInterface
{
    /**
     * @param $parent object
     */
    public function setParentDocument($parent);

    /**
     * @return object
     */
    public function getParentDocument();
}
