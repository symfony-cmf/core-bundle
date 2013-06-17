<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\Twig;

use Symfony\Cmf\Bundle\CoreBundle\Twig\TwigExtension;

class TwigExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $pwc;
    private $managerRegistry;
    private $manager;
    private $uow;
    private $extension;

    public function setUp()
    {
        $this->pwc = $this->getMock('Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowCheckerInterface');

        $this->managerRegistry = $this->getMockBuilder('Doctrine\Bundle\PHPCRBundle\ManagerRegistry')
            ->disableOriginalConstructor()
            ->setMethods(array('getManager'))
            ->getMock()
        ;

        $this->manager = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->managerRegistry->expects($this->any())
            ->method('getManager')
            ->with('foo')
            ->will($this->returnValue($this->manager))
        ;

        $this->uow = $this->getMockBuilder('Doctrine\ODM\PHPCR\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->manager->expects($this->any())
            ->method('getUnitOfWork')
            ->with()
            ->will($this->returnValue($this->uow))
        ;

        $this->extension = new TwigExtension($this->pwc, $this->managerRegistry, 'foo');
    }

    public function testGetFunctions()
    {
        $extension = new TwigExtension($this->pwc);
        $this->assertCount(1, $extension->getFunctions());

        $this->assertCount(15, $this->extension->getFunctions());
    }

    public function testGetNodeName()
    {
        $document = new \stdClass();

        $this->assertEquals(false, $this->extension->getNodeName($document));

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($document)
            ->will($this->returnValue('/foo/bar'))
        ;

        $this->assertEquals('bar', $this->extension->getNodeName($document));
    }

    public function testGetParentPath()
    {
        $document = new \stdClass();

        $this->assertEquals(false, $this->extension->getParentPath($document));

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($document)
            ->will($this->returnValue('/foo/bar'))
        ;

        $this->assertEquals('/foo', $this->extension->getParentPath($document));
    }

    public function testGetPath()
    {
        $document = new \stdClass();

        $this->assertEquals(null, $this->extension->getPath($document));

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($document)
            ->will($this->returnValue('/foo/bar'))
        ;

        $this->assertEquals('/foo/bar', $this->extension->getPath($document));
    }

    public function testFind()
    {
        $document = new \stdClass();

        $this->manager->expects($this->any())
            ->method('find')
            ->with(null, '/foo')
            ->will($this->onConsecutiveCalls(null, $document))
        ;

        $this->assertNull($this->extension->find('/foo'));
        $this->assertEquals($document, $this->extension->find('/foo'));
    }

    public function testFindMany()
    {
        $this->assertEquals(array(), $this->extension->findMany());
    }

    public function testFindManyFilterClass()
    {
        $documentA = new \stdClass();
        $documentB = new \stdClass();

        $this->manager->expects($this->any())
            ->method('find')
            ->will($this->onConsecutiveCalls($documentA, null, $documentA, $documentB))
        ;

        $this->assertEquals(array(), $this->extension->findMany(array('/foo', 'bar'), false, false, null, 'Exception'));
        $this->assertEquals(array($documentA, $documentB), $this->extension->findMany(array('/foo', 'bar'), false, false, null, 'stdClass'));
    }

    public function testFindManyIgnoreRole()
    {
        $documentA = new \stdClass();
        $documentB = new \stdClass();

        $this->manager->expects($this->any())
            ->method('find')
            ->will($this->onConsecutiveCalls($documentA, null, $documentA, $documentB))
        ;

        $this->pwc->expects($this->any())
            ->method('checkIsPublished')
            ->with($documentA)
            ->will($this->onConsecutiveCalls(false, true))
        ;

        $this->assertEquals(array($documentA), $this->extension->findMany(array('/foo', 'bar'), false, false, null));
        $this->assertEquals(array($documentB), $this->extension->findMany(array('/foo', 'bar'), false, false, false));
    }

    public function testFindManyLimitOffset()
    {
        $documentA = new \stdClass();
        $documentB = new \stdClass();

        $this->manager->expects($this->any())
            ->method('find')
            ->will($this->onConsecutiveCalls($documentA, $documentB, $documentA, $documentB, $documentA, $documentB))
        ;

        $this->assertEquals(array($documentA), $this->extension->findMany(array('/foo', 'bar'), 1, false, null));
        $this->assertEquals(array($documentB), $this->extension->findMany(array('/foo', 'bar'), false, 1, null));
        $this->assertEquals(array($documentB), $this->extension->findMany(array('/foo', 'bar'), 1, 1, null));
    }

    public function testIsPublished()
    {
        $this->assertFalse($this->extension->isPublished(null));

        $document = new \stdClass();

        $this->pwc->expects($this->any())
            ->method('checkIsPublished')
            ->with($document)
            ->will($this->onConsecutiveCalls(false, true))
        ;

        $this->assertFalse($this->extension->isPublished($document));
        $this->assertTrue($this->extension->isPublished($document));
    }

    public function testGetLocalesFor()
    {
        $this->assertEquals(array(), $this->extension->getLocalesFor(null));

        $document = new \stdClass();

        $this->manager->expects($this->any())
            ->method('find')
            ->with(null, '/foo')
            ->will($this->onConsecutiveCalls(null, $document))
        ;

        $this->assertEquals(array(), $this->extension->getLocalesFor('/foo'));

        $this->manager->expects($this->once())
            ->method('getLocalesFor')
            ->with($document)
            ->will($this->returnValue(array('en', 'de')))
        ;

        $this->assertEquals(array('en', 'de'), $this->extension->getLocalesFor('/foo'));
    }

    public function testGetLocalesForMissingTranslationException()
    {
        $this->markTestIncomplete('TODO: write test');
    }

    public function testGetChild()
    {
        $parent = new \stdClass();

        $this->assertEquals(null, $this->extension->getChild($parent, 'bar'));

        $child = new \stdClass();

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($parent)
            ->will($this->returnValue('/foo'))
        ;

        $this->manager->expects($this->once())
            ->method('find')
            ->with(null, '/foo/bar')
            ->will($this->returnValue($child))
        ;

        $this->assertEquals($child, $this->extension->getChild($parent, 'bar'));
    }

    public function testGetChildren()
    {
        $parent = new \stdClass();
        $child = new \stdClass();

        $this->manager->expects($this->any())
            ->method('getChildren')
            ->with($parent)
            ->will($this->returnValue(array($child)))
        ;

        $this->assertEquals(array(), $this->extension->getChildren(null));
        $this->assertEquals(array(), $this->extension->getChildren($parent, false, false, true));
    }

    public function testGetChildrenFilterClass()
    {
        $this->markTestIncomplete('TODO: write test');
    }

    public function testGetChildrenIgnoreRole()
    {
        $this->markTestIncomplete('TODO: write test');
    }

    public function testGetChildrenLimitOffset()
    {
        $this->markTestIncomplete('TODO: write test');
    }

    public function testGetLinkableChildren()
    {
        $this->assertEquals(array(), $this->extension->getLinkableChildren(null));

        $this->markTestIncomplete('TODO: write test');
    }
}
