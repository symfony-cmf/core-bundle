<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document;

use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;

/**
 * @PHPCRODM\Document(referenceable=true)
 */
class Content implements RouteReferrersReadInterface
{
    /** @PHPCRODM\Id */
    public $id;

    /** @PHPCRODM\Referrers(referringDocument="Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route", referencedBy="content") */
    public $routes;

    public function getId()
    {
        return $this->id;
    }

    public function getRoutes()
    {
        return $this->routes;
    }
}
