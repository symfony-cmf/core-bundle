<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Model;

/**
 * An interface for models with a parent object.
 *
 * Note that PHPCR-ODM documents will most likely use the HierarchyInterface
 * of PHPCR-ODM instead.
 */
interface ChildInterface
{
    /**
     * @param $parent object
     */
    public function setParentObject($parent);

    /**
     * @return object
     */
    public function getParentObject();
}
