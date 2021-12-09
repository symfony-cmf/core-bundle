<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\Templating\Helper;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Cmf\Bundle\CoreBundle\Templating\Helper\Cmf;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CmfTest extends TestCase
{
    private $pwc;

    private $managerRegistry;

    private $manager;

    private $uow;

    /**
     * @var Cmf
     */
    private $helper;

    public function setUp(): void
    {
        $this->pwc = $this->createMock(AuthorizationCheckerInterface::class);

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);

        $this->manager = $this->createMock(DocumentManager::class);

        $this->managerRegistry->expects($this->any())
            ->method('getManager')
            ->with('foo')
            ->will($this->returnValue($this->manager))
        ;

        $this->uow = $this->createMock(UnitOfWork::class);

        $this->manager->expects($this->any())
            ->method('getUnitOfWork')
            ->with()
            ->will($this->returnValue($this->uow))
        ;

        $this->helper = new Cmf($this->pwc);
        $this->helper->setDoctrineRegistry($this->managerRegistry, 'foo');
    }

    public function testGetNodeName()
    {
        $document = new \stdClass();

        $this->uow->expects(self::exactly(2))
            ->method('getDocumentId')
            ->withConsecutive([$document], [$document])
            ->willReturnOnConsecutiveCalls($this->throwException(new \Exception()), $this->returnValue('/foo/bar'))
        ;

        $this->assertFalse($this->helper->getNodeName($document));
        $this->assertEquals('bar', $this->helper->getNodeName($document));
    }

    public function testGetParentPath()
    {
        $document = new \stdClass();

        $this->assertFalse($this->helper->getParentPath($document));

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($document)
            ->will($this->returnValue('/foo/bar'))
        ;

        $this->assertEquals('/foo', $this->helper->getParentPath($document));
    }

    public function testGetPath()
    {
        $document = new \stdClass();

        $this->assertNull($this->helper->getPath($document));

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($document)
            ->will($this->returnValue('/foo/bar'))
        ;

        $this->assertEquals('/foo/bar', $this->helper->getPath($document));
    }

    public function testGetPathInvalid()
    {
        $document = new \stdClass();

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($document)
            ->will($this->throwException(new \Exception('test')));

        $this->assertFalse($this->helper->getPath($document));
    }

    public function testFind()
    {
        $document = new \stdClass();

        $this->manager->expects($this->any())
            ->method('find')
            ->with(null, '/foo')
            ->will($this->onConsecutiveCalls(null, $document))
        ;

        $this->assertNull($this->helper->find('/foo'));
        $this->assertEquals($document, $this->helper->find('/foo'));
    }

    public function testFindTranslation()
    {
        $document = new \stdClass();

        $this->manager->expects($this->any())
            ->method('findTranslation')
            ->with(null, '/foo', 'en')
            ->will($this->onConsecutiveCalls(null, $document, 'en'))
        ;

        $this->assertNull($this->helper->findTranslation('/foo', 'en'));
        $this->assertEquals($document, $this->helper->findTranslation('/foo', 'en'));
    }

    public function testFindMany()
    {
        $this->assertEquals([], $this->helper->findMany());
    }

    public function testFindManyFilterClass()
    {
        $documentA = new \stdClass();
        $documentB = new \stdClass();

        $this->manager->expects($this->any())
            ->method('find')
            ->will($this->onConsecutiveCalls($documentA, null, $documentA, $documentB))
        ;

        $this->assertEquals([], $this->helper->findMany(['/foo', 'bar'], false, false, null, 'Exception'));
        $this->assertEquals([$documentA, $documentB], $this->helper->findMany(['/foo', 'bar'], false, false, null, 'stdClass'));
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

        $this->assertEquals([$documentB], $this->helper->findMany(['/foo', '/bar'], false, false, true));
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

        $this->assertEquals([$documentA, $documentB], $this->helper->findMany(['/foo', '/bar'], false, false, null));
    }

    public function testFindManyLimitOffset()
    {
        $documentA = new \stdClass();
        $documentB = new \stdClass();

        $this->manager->expects($this->any())
            ->method('find')
            ->will($this->onConsecutiveCalls($documentA, $documentB, $documentA, $documentB, $documentA, $documentB))
        ;

        $this->assertEquals([$documentA], $this->helper->findMany(['/foo', 'bar'], 1, false, null));
        $this->assertEquals([$documentB], $this->helper->findMany(['/foo', 'bar'], false, 1, null));
        $this->assertEquals([$documentB], $this->helper->findMany(['/foo', 'bar'], 1, 1, null));
    }

    public function testFindManyNoWorkflow()
    {
        $extension = new Cmf(null);
        $extension->setDoctrineRegistry($this->managerRegistry, 'foo');

        $documentA = new \stdClass();

        $this->manager->expects($this->any())
            ->method('find')
            ->with(null, '/foo')
            ->will($this->returnValue($documentA))
        ;

        $this->expectException(InvalidConfigurationException::class);
        $extension->findMany(['/foo', '/bar'], false, false);
    }

    public function testIsPublished()
    {
        $this->assertFalse($this->helper->isPublished(null));

        $document = new \stdClass();

        $this->pwc->expects($this->any())
            ->method('isGranted')
            ->with(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $document)
            ->will($this->onConsecutiveCalls(false, true))
        ;

        $this->assertFalse($this->helper->isPublished($document));
        $this->assertTrue($this->helper->isPublished($document));
    }

    public function testIsPublishedNoWorkflow()
    {
        $extension = new Cmf(null);
        $extension->setDoctrineRegistry($this->managerRegistry, 'foo');

        $this->expectException(InvalidConfigurationException::class);
        $extension->isPublished(new \stdClass());
    }

    public function testIsLinkable()
    {
        $this->assertFalse($this->helper->isLinkable(null));
        $this->assertFalse($this->helper->isLinkable('a'));
        $this->assertFalse($this->helper->isLinkable($this));

        $content = $this->createMock(RouteReferrersReadInterface::class);
        $content
            ->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue([]))
        ;
        $this->assertFalse($this->helper->isLinkable($content));

        $route = $this->createMock(Route::class);
        $content = $this->createMock(RouteReferrersReadInterface::class);
        $content
            ->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue([$route]))
        ;
        $this->assertTrue($this->helper->isLinkable($content));
    }

    public function testGetLocalesFor()
    {
        $this->assertEquals([], $this->helper->getLocalesFor(null));

        $document = new \stdClass();

        $this->manager->expects($this->any())
            ->method('find')
            ->with(null, '/foo')
            ->will($this->onConsecutiveCalls(null, $document))
        ;

        $this->assertEquals([], $this->helper->getLocalesFor('/foo'));

        $this->manager->expects($this->once())
            ->method('getLocalesFor')
            ->with($document)
            ->will($this->returnValue(['en', 'de']))
        ;

        $this->assertEquals(['en', 'de'], $this->helper->getLocalesFor('/foo'));
    }

    public function testGetLocalesForMissingTranslationException()
    {
        $this->markTestIncomplete('TODO: write test');
    }

    public function testGetChild()
    {
        $parent = new \stdClass();

        $this->assertNull($this->helper->getChild($parent, 'bar'));

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

        $this->assertEquals($child, $this->helper->getChild($parent, 'bar'));
    }

    public function testGetChildError()
    {
        $parent = new \stdClass();

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($parent)
            ->will($this->throwException(new \Exception('test')))
        ;

        $this->assertFalse($this->helper->getChild($parent, 'bar'));
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
        $this->assertEquals([], $this->helper->getLinkableChildren(null));

        $this->markTestIncomplete('TODO: write test');
    }
}
