<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Fixtures\App\Document;

use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\PHPCR\Mapping\Attributes as PHPCRODM;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;

#[PHPCRODM\Document(referenceable: true)]
class Content implements RouteReferrersReadInterface
{
    #[PHPCRODM\Id]
    public string $id;

    #[PHPCRODM\Referrers(referencedBy: 'content', referringDocument: Route::class)]
    public array|Collection $routes;

    public function getId(): string
    {
        return $this->id;
    }

    public function getRoutes(): iterable
    {
        return $this->routes;
    }
}
