<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Cmf\Bundle\CoreBundle\Model\ChildInterface;
use Doctrine\ODM\PHPCR\HierarchyInterface;

/**
 * Admin extension to handle child models.
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 */
class ChildExtension extends AdminExtension
{
    /**
     * Set a default parent if defined in the request.
     *
     * {@inheritdoc}
     */
    public function alterNewInstance(AdminInterface $admin, $object)
    {
        if (!$admin->hasRequest()
            || !$parentId = $admin->getRequest()->get('parent')
        ) {
            return;
        }

        $parent = $admin->getModelManager()->find(null, $parentId);
        if (!$parent) {
            return;
        }

        switch ($object) {
            case $object instanceof HierarchyInterface:
                $object->setParentDocument($parent);

                break;
            case $object instanceof ChildInterface:
                $object->setParentObject($parent);

                break;
            default:
                throw new \InvalidArgumentException(sprintf('Class %s is not supported', get_class($object)));
        }
    }
}
