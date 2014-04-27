<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document;

use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;

/**
* @PHPCRODM\Document()
*/
class RouteAware implements RouteReferrersReadInterface
{
    /** @PHPCRODM\Id */
    public $id;

    public function getId()
    {
        return $this->id;
    }

    public function getRoutes()
    {
        return array(1, 2);
    }
}
