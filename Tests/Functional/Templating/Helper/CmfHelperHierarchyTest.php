<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\Templating\Helper;

use Symfony\Cmf\Bundle\CoreBundle\Templating\Helper\CmfHelper;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Component\Security\Core\SecurityContextInterface;

class CmfHelperHierarchyTest extends BaseTestCase
{
    /**
     * @var SecurityContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pwc;

    /**
     * @var CmfHelper
     */
    private $helper;

    public function setUp()
    {
        $dbManager = $this->db('PHPCR');
        $dbManager->loadFixtures(array('Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\DataFixture\LoadHierarchyRouteData'));

        $this->pwc = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->pwc->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(true))
        ;

        $this->helper = new CmfHelper($this->pwc);
        $this->helper->setDoctrineRegistry($dbManager->getRegistry(), 'default');
    }

    public function testGetDescendants()
    {
        $this->assertEquals(array(), $this->helper->getDescendants(null));

        $expected = array('/a/b', '/a/b/c', '/a/b/d', '/a/b/e', '/a/f', '/a/f/g', '/a/f/g/h', '/a/i');
        $this->assertEquals($expected, $this->helper->getDescendants('/a'));

        $expected = array('/a/b', '/a/f', '/a/i');
        $this->assertEquals($expected, $this->helper->getDescendants('/a', 1));
    }

    /**
     * @dataProvider getPrevData
     */
    public function testGetPrev($expected, $path, $anchor = null, $depth = null, $class = 'Doctrine\ODM\PHPCR\Document\Generic')
    {
        $prev = $this->helper->getPrev($path, $anchor, $depth);
        if (null === $expected) {
            $this->assertNull($prev);
        } else {
            $this->assertInstanceOf($class, $prev);
            $this->assertEquals($expected, $prev->getId());
        }
    }

    public static function getPrevData()
    {
        return array(
            array(null, null),
            array(null, '/a'),
            array(null, '/a/b'),
            array(null, '/a/b/c'),
            array('/a/b/c', '/a/b/d', null, null, 'Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document\RouteAware'),
            array('/a/b/d', '/a/b/e'),
            array('/a/b', '/a/f'),
            array(null, '/a/f/g'),
            array(null, '/a/f/g/h'),
            array(null, '/a', '/a'),
            array('/a', '/a/b', '/a'),
            array('/a/b', '/a/b/c', '/a'),
            array('/a/b/c', '/a/b/d', '/a', null, 'Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document\RouteAware'),
            array('/a/b/d', '/a/b/e', '/a'),
            array('/a/b/e', '/a/f', '/a', null, 'Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document\RouteAware'),
            array('/a/f', '/a/f/g', '/a'),
            array('/a/f/g', '/a/f/g/h', '/a'),
            array('/a/f/g/h', '/a/i', '/a'),
            array('/a/f/g', '/a/i', '/a', 2),
        );
    }

    /**
     * @dataProvider getNextData
     */
    public function testGetNext($expected, $path, $anchor = null, $depth = null, $class = 'Doctrine\ODM\PHPCR\Document\Generic')
    {
        $next = $this->helper->getNext($path, $anchor, $depth);
        if (null === $expected) {
            $this->assertNull($next);
        } else {
            $this->assertInstanceOf($class, $next);
            $this->assertEquals($expected, $next->getId());
        }
    }

    public static function getNextData()
    {
        return array(
            array(null, null),
            array(null, '/a'),
            array('/a/f', '/a/b'),
            array('/a/b/d', '/a/b/c'),
            array('/a/b/e', '/a/b/d', null, null, 'Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document\RouteAware'),
            array(null, '/a/b/e'),
            array('/a/i', '/a/f'),
            array(null, '/a/f/g'),
            array(null, '/a/f/g/h'),
            array('/a/b', '/a', '/a'),
            array('/a/b/c', '/a/b', '/a', null, 'Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document\RouteAware'),
            array('/a/b/d', '/a/b/c', '/a'),
            array('/a/b/e', '/a/b/d', '/a', null, 'Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document\RouteAware'),
            array('/a/f', '/a/b/e', '/a'),
            array('/a/f/g', '/a/f', '/a'),
            array('/a/f/g/h', '/a/f/g', '/a'),
            array('/a/i', '/a/f/g/h', '/a'),
            array(null, '/a/i', '/a'),
            array(null, '/a/b/e', '/a/b'),
            array('/a/i', '/a/f/g', '/a', 2),
        );
    }

    /**
     * @dataProvider getPrevLinkableData
     */
    public function testGetPrevLinkable($expected, $path, $anchor = null, $depth = null)
    {
        $prev = $this->helper->getPrevLinkable($path, $anchor, $depth);
        if (null === $expected) {
            $this->assertNull($prev);
        } else {
            $this->assertInstanceOf('Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document\RouteAware', $prev);
            $this->assertEquals($expected, $prev->getId());
        }
    }

    public static function getPrevLinkableData()
    {
        // TODO: expand test case
        return array(
            array(null, null),
            array(null, '/a/b/c'),
            array('/a/b/c', '/a/b/d'),
            array('/a/b/c', '/a/b/e'),
        );
    }

    /**
     * @dataProvider getNextLinkableData
     */
    public function testGetNextLinkable($expected, $path, $anchor = null, $depth = null)
    {
        $next = $this->helper->getNextLinkable($path, $anchor, $depth);
        if (null === $expected) {
            $this->assertNull($next);
        } else {
            $this->assertInstanceOf('Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document\RouteAware', $next);
            $this->assertEquals($expected, $next->getId());
        }
    }

    public static function getNextLinkableData()
    {
        // TODO: expand test case
        return array(
            array(null, null),
            array('/a/b/e', '/a/b/c'),
            array('/a/b/e', '/a/b/d'),
            array(null, '/a/b/e'),
        );
    }
}
