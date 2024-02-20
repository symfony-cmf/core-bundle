<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\DataFixture;

use Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document\Publishable;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;

use Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document\Content;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\PHPCR\Document\Generic;

/**
 * Fixtures class for test data.
 */
class LoadPublishableData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $doc = new Publishable(true, new \DateTime('yesterday'));
        $doc->id = '/published';
        $manager->persist($doc);

        $doc = new Publishable(false);
        $doc->id = '/unpublishable';
        $manager->persist($doc);

        $doc = new Publishable(true, new \DateTime('tomorrow'));
        $doc->id = '/timeperiod';
        $manager->persist($doc);

        $manager->flush();
    }
}
