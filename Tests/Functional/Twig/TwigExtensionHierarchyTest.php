<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\Twig;

use Symfony\Cmf\Bundle\CoreBundle\Twig\TwigExtension;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class TwigExtensionHierarchyTest extends BaseTestCase
{
    private $pwc;
    private $extension;

    public function setUp()
    {
        $container = $this->getContainer();
        $managerRegistry = $container->get('doctrine_phpcr');
        $session = $managerRegistry->getConnection();
        $root = $session->getRootNode();
        if ($root->hasNode('a')) {
            $session->removeItem('/a');
        }

        $a = $root->addNode('a');
        $b = $a->addNode('b');
        $c = $b->addNode('c');
        $d = $b->addNode('d');
        $e = $b->addNode('e');
        $f = $a->addNode('f');
        $g = $f->addNode('g');
        $h = $g->addNode('h');

        $session->save();

        $this->pwc = $this->getMock('Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowCheckerInterface');
        $this->pwc->expects($this->any())
            ->method('checkIsPublished')
            ->will($this->returnValue(true));

        $this->extension = new TwigExtension($this->pwc, $managerRegistry, 'default');
    }

    public function testGetDescendants()
    {
        $this->assertEquals(array(), $this->extension->getDescendants(null));

        $this->assertEquals(array('/a/b', '/a/b/c', '/a/b/d', '/a/b/e', '/a/f', '/a/f/g', '/a/f/g/h'), $this->extension->getDescendants('/a'));

        $this->assertEquals(array('/a/b', '/a/f'), $this->extension->getDescendants('/a', 1));
    }

    /**
     * @dataProvider getPrevData
     */
    public function testGetPrev($expected, $path)
    {
        $prev = $this->extension->getPrev($path);
        if (null === $expected) {
            $this->assertNull($prev);
        } else {
            $this->assertInstanceOf('Doctrine\ODM\PHPCR\Document\Generic', $prev);
            $this->assertEquals($expected, $prev->getId());
        }
    }

    public static function getPrevData()
    {
        return array(
            array(null, null),
            array(null, '/a'),
            array('/a', '/a/b'),
            array('/a/b', '/a/b/c'),
            array('/a/b/c', '/a/b/d'),
            array('/a/b/d', '/a/b/e'),
            array('/a/b/e', '/a/f'),
            array('/a/f', '/a/f/g'),
            array('/a/f/g', '/a/f/g/h'),
        );
    }

    /**
     * @dataProvider getNextData
     */
    public function testGetNext($expected, $path)
    {
        $next = $this->extension->getNext($path);
        if (null === $expected) {
            $this->assertNull($next);
        } else {
            $this->assertInstanceOf('Doctrine\ODM\PHPCR\Document\Generic', $next);
            $this->assertEquals($expected, $next->getId());
        }
    }

    public static function getNextData()
    {
        return array(
            array(null, null),
            array('/a/b', '/a'),
            array('/a/b/c', '/a/b'),
            array('/a/b/d', '/a/b/c'),
            array('/a/b/e', '/a/b/d'),
            array('/a/f', '/a/b/e'),
            array('/a/f/g', '/a/f'),
            array('/a/f/g/h', '/a/f/g'),
            array(null, '/a/f/g/h'),
        );
    }

    public function testGetPrevLinkable()
    {
        $this->assertNull($this->extension->getPrevLinkable(null));

        $this->markTestIncomplete('TODO: write test');
    }

    public function testGetNextLinkable()
    {
        $this->assertNull($this->extension->getNextLinkable(null));

        $this->markTestIncomplete('TODO: write test');
    }
}
