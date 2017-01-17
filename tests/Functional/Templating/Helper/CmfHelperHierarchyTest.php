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

use Doctrine\ODM\PHPCR\Document\Generic;
use Symfony\Cmf\Bundle\CoreBundle\Templating\Helper\CmfHelper;
use Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\DataFixture\LoadHierarchyRouteData;
use Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document\RouteAware;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class CmfHelperHierarchyTest extends BaseTestCase
{
    /**
     * @var SecurityContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $publishWorkflowChecker;

    /**
     * @var CmfHelper
     */
    private $helper;

    public function setUp()
    {
        $dbManager = $this->db('PHPCR');
        $dbManager->loadFixtures([LoadHierarchyRouteData::class]);

        $this->publishWorkflowChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->publishWorkflowChecker->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(true))
        ;

        $this->helper = new CmfHelper($this->publishWorkflowChecker);
        $this->helper->setDoctrineRegistry($dbManager->getRegistry(), 'default');
    }

    public function testGetDescendants()
    {
        $this->assertEquals([], $this->helper->getDescendants(null));

        $expected = ['/a/b', '/a/b/c', '/a/b/d', '/a/b/e', '/a/f', '/a/f/g', '/a/f/g/h', '/a/i'];
        $this->assertEquals($expected, $this->helper->getDescendants('/a'));

        $expected = ['/a/b', '/a/f', '/a/i'];
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
        return [
            [null, null],
            [null, '/a'],
            [null, '/a/b'],
            [null, '/a/b/c'],
            ['/a/b/c', '/a/b/d', null, null, RouteAware::class],
            ['/a/b/d', '/a/b/e'],
            ['/a/b', '/a/f'],
            [null, '/a/f/g'],
            [null, '/a/f/g/h'],
            [null, '/a', '/a'],
            ['/a', '/a/b', '/a'],
            ['/a/b', '/a/b/c', '/a'],
            ['/a/b/c', '/a/b/d', '/a', null, RouteAware::class],
            ['/a/b/d', '/a/b/e', '/a'],
            ['/a/b/e', '/a/f', '/a', null, RouteAware::class],
            ['/a/f', '/a/f/g', '/a'],
            ['/a/f/g', '/a/f/g/h', '/a'],
            ['/a/f/g/h', '/a/i', '/a'],
            ['/a/f/g', '/a/i', '/a', 2],
        ];
    }

    /**
     * @dataProvider getNextData
     */
    public function testGetNext($expected, $path, $anchor = null, $depth = null, $class = Generic::class)
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
        return [
            [null, null],
            [null, '/a'],
            ['/a/f', '/a/b'],
            ['/a/b/d', '/a/b/c'],
            ['/a/b/e', '/a/b/d', null, null, RouteAware::class],
            [null, '/a/b/e'],
            ['/a/i', '/a/f'],
            [null, '/a/f/g'],
            [null, '/a/f/g/h'],
            ['/a/b', '/a', '/a'],
            ['/a/b/c', '/a/b', '/a', null, RouteAware::class],
            ['/a/b/d', '/a/b/c', '/a'],
            ['/a/b/e', '/a/b/d', '/a', null, RouteAware::class],
            ['/a/f', '/a/b/e', '/a'],
            ['/a/f/g', '/a/f', '/a'],
            ['/a/f/g/h', '/a/f/g', '/a'],
            ['/a/i', '/a/f/g/h', '/a'],
            [null, '/a/i', '/a'],
            [null, '/a/b/e', '/a/b'],
            ['/a/i', '/a/f/g', '/a', 2],
        ];
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
            $this->assertInstanceOf(RouteAware::class, $prev);
            $this->assertEquals($expected, $prev->getId());
        }
    }

    public static function getPrevLinkableData()
    {
        // TODO: expand test case
        return [
            [null, null],
            [null, '/a/b/c'],
            ['/a/b/c', '/a/b/d'],
            ['/a/b/c', '/a/b/e'],
        ];
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
            $this->assertInstanceOf(RouteAware::class, $next);
            $this->assertEquals($expected, $next->getId());
        }
    }

    public static function getNextLinkableData()
    {
        // TODO: expand test case
        return [
            [null, null],
            ['/a/b/e', '/a/b/c'],
            ['/a/b/e', '/a/b/d'],
            [null, '/a/b/e'],
        ];
    }
}
