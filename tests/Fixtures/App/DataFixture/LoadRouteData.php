<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Fixtures\App\DataFixture;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\PHPCR\Document\Generic;
use Symfony\Cmf\Bundle\CoreBundle\Tests\Fixtures\App\Document\Content;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;

/**
 * Fixtures class for test data.
 */
class LoadRouteData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $root = $manager->find(null, '/');

        $test = new Generic();
        $test->setNodename('test');
        $test->setParentDocument($root);
        $manager->persist($test);

        $content = new Generic();
        $content->setNodename('content');
        $content->setParentDocument($test);
        $manager->persist($content);

        $aContent = new Content();
        $aContent->id = '/test/content/a';
        $manager->persist($aContent);

        $bContent = new Content();
        $bContent->id = '/test/content/b';
        $manager->persist($bContent);

        $cms = new Generic();
        $cms->setNodename('cms');
        $cms->setParentDocument($test);
        $manager->persist($cms);

        $routes = new Generic();
        $routes->setNodename('routes');
        $routes->setParentDocument($cms);
        $manager->persist($routes);

        $aRoute = new Route();
        $aRoute->setName('a');
        $aRoute->setParentDocument($routes);
        $aRoute->setContent($aContent);
        $manager->persist($aRoute);
        $bRoute = new Route();
        $bRoute->setName('b');
        $bRoute->setParentDocument($routes);
        $bRoute->setContent($bContent);
        $manager->persist($bRoute);
        $manager->flush();
    }
}
