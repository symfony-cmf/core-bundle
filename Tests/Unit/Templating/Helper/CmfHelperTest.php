<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\Templating\Helper;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Cmf\Bundle\CoreBundle\Templating\Helper\CmfHelper;

class CmfHelperTest extends \PHPUnit_Framework_TestCase
{
    private $pwc;
    private $managerRegistry;
    private $manager;
    private $uow;
    /**
     * @var CmfHelper
     */
    private $extension;

    public function setUp()
    {
        $this->pwc = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');

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

        $this->extension = new CmfHelper($this->pwc, $this->managerRegistry, 'foo');
    }

    public function testGetNodeName()
    {
        $document = new \stdClass();

        $this->uow->expects($this->at(0))
            ->method('getDocumentId')
            ->with($document)
            ->will($this->throwException(new \Exception()))
        ;

        $this->uow->expects($this->at(1))
            ->method('getDocumentId')
            ->with($document)
            ->will($this->returnValue('/foo/bar'))
        ;

        $this->assertEquals(false, $this->extension->getNodeName($document));
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

    public function testGetPathInvalid()
    {
        $document = new \stdClass();

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($document)
            ->will($this->throwException(new \Exception('test')));
        ;

        $this->assertFalse($this->extension->getPath($document));
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
            ->will($this->onConsecutiveCalls($documentA, $documentB))
        ;

        $this->pwc->expects($this->any())
            ->method('isGranted')
            ->will($this->onConsecutiveCalls(false, true))
        ;

        $this->assertEquals(array($documentB), $this->extension->findMany(array('/foo', '/bar'), false, false, true));
    }

    public function testFindManyIgnoreWorkflow()
    {
        $documentA = new \stdClass();
        $documentB = new \stdClass();

        $this->manager->expects($this->any())
            ->method('find')
            ->will($this->onConsecutiveCalls($documentA, $documentB))
        ;

        $this->pwc->expects($this->never())
            ->method('isGranted')
        ;

        $this->assertEquals(array($documentA, $documentB), $this->extension->findMany(array('/foo', '/bar'), false, false, null));
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

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testFindManyNoWorkflow()
    {
        $this->extension = new CmfHelper(null, $this->managerRegistry, 'foo');

        $documentA = new \stdClass();

        $this->manager->expects($this->any())
            ->method('find')
            ->with(null, '/foo')
            ->will($this->returnValue($documentA))
        ;

        $this->extension->findMany(array('/foo', '/bar'), false, false);
    }

    public function testIsPublished()
    {
        $this->assertFalse($this->extension->isPublished(null));

        $document = new \stdClass();

        $this->pwc->expects($this->any())
            ->method('isGranted')
            ->with(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $document)
            ->will($this->onConsecutiveCalls(false, true))
        ;

        $this->assertFalse($this->extension->isPublished($document));
        $this->assertTrue($this->extension->isPublished($document));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testIsPublishedNoWorkflow()
    {
        $this->extension = new CmfHelper(null, $this->managerRegistry, 'foo');
        $this->extension->isPublished(new \stdClass());
    }

    public function testIsLinkable()
    {
        $this->assertFalse($this->extension->isLinkable(null));
        $this->assertFalse($this->extension->isLinkable('a'));
        $this->assertFalse($this->extension->isLinkable($this));

        $content = $this->getMock('Symfony\Cmf\Component\Routing\RouteReferrersReadInterface');
        $content
            ->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array()))
        ;
        $this->assertFalse($this->extension->isLinkable($content));

        $route = $this->getMockBuilder('Symfony\Component\Routing\Route')->disableOriginalConstructor()->getMock();
        $content = $this->getMock('Symfony\Cmf\Component\Routing\RouteReferrersReadInterface');
        $content
            ->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array($route)))
        ;
        $this->assertTrue($this->extension->isLinkable($content));
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

    public function testGetChildError()
    {
        $parent = new \stdClass();

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($parent)
            ->will($this->throwException(new \Exception('test')))
        ;

        $this->assertFalse($this->extension->getChild($parent, 'bar'));
    }

    public function testGetChildren()
    {
        $this->markTestIncomplete('TODO: write test');
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
