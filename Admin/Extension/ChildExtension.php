<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Cmf\Bundle\CoreBundle\Model\ChildInterface;

/**
 * Admin extension to handle child models.
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 */
class ChildExtension extends AdminExtension
{
    /**
     * Set a default parent if defined in the request
     *
     * {@inheritDoc}
     */
    public function alterNewInstance(AdminInterface $admin, $object)
    {
        if (!$object instanceof ChildInterface) {
            throw new \InvalidArgumentException('Expected ChildInterface, got ' . get_class($object));
        }

        if ($admin->hasRequest() && $parentId = $admin->getRequest()->get('parent')) {
            if ($parent = $admin->getModelManager()->find(null, $parentId)) {
                $object->setParentDocument($parent);
            }
        }
    }
}
