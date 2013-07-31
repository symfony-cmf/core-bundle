<?php

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
    }
}
