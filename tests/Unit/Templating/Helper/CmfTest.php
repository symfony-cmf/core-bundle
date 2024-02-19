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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Cmf\Bundle\CoreBundle\Templating\Helper\Cmf;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CmfTest extends TestCase
{
    private AuthorizationCheckerInterface&MockObject $pwc;

    private ManagerRegistry&MockObject $managerRegistry;

    private DocumentManager&MockObject $manager;

    private UnitOfWork&MockObject $uow;

    private Cmf $helper;

    public function setUp(): void
    {
        $this->pwc = $this->createMock(AuthorizationCheckerInterface::class);

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);

        $this->manager = $this->createMock(DocumentManager::class);

        $this->managerRegistry
            ->method('getManager')
            ->with('foo')
            ->willReturn($this->manager)
        ;

        $this->uow = $this->createMock(UnitOfWork::class);

        $this->manager
            ->method('getUnitOfWork')
            ->with()
            ->willReturn($this->uow)
        ;

        $this->helper = new Cmf($this->pwc);
        $this->helper->setDoctrineRegistry($this->managerRegistry, 'foo');
    }

    public function testGetNodeName(): void
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

    public function testGetParentPath(): void
    {
        $document = new \stdClass();

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($document)
            ->willReturn('/foo/bar')
        ;

        $this->assertEquals('/foo', $this->helper->getParentPath($document));
    }

    public function testGetParentPathNotManaged(): void
    {
        $document = new \stdClass();

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($document)
            ->willThrowException(new \Exception())
        ;

        $this->assertFalse($this->helper->getParentPath($document));
    }

    public function testGetPath(): void
    {
        $document = new \stdClass();

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($document)
            ->willReturn('/foo/bar')
        ;

        $this->assertEquals('/foo/bar', $this->helper->getPath($document));
    }

    public function testGetPathNotManaged(): void
    {
        $document = new \stdClass();

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($document)
            ->willThrowException(new \Exception())
        ;
        $this->assertFalse($this->helper->getPath($document));
    }

    public function testGetPathInvalid(): void
    {
        $document = new \stdClass();

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($document)
            ->will($this->throwException(new \Exception('test')));

        $this->assertFalse($this->helper->getPath($document));
    }

    public function testFind(): void
    {
        $document = new \stdClass();

        $this->manager
            ->method('find')
            ->with(null, '/foo')
            ->willReturnOnConsecutiveCalls(null, $document)
        ;

        $this->assertNull($this->helper->find('/foo'));
        $this->assertEquals($document, $this->helper->find('/foo'));
    }

    public function testFindTranslation(): void
    {
        $document = new \stdClass();

        $this->manager
            ->method('findTranslation')
            ->with(null, '/foo', 'en')
            ->willReturnOnConsecutiveCalls(null, $document, 'en')
        ;

        $this->assertNull($this->helper->findTranslation('/foo', 'en'));
        $this->assertEquals($document, $this->helper->findTranslation('/foo', 'en'));
    }

    public function testFindMany(): void
    {
        $this->assertEquals([], $this->helper->findMany());
    }

    public function testFindManyFilterClass(): void
    {
        $documentA = new \stdClass();
        $documentB = new \stdClass();

        $this->manager
            ->method('find')
            ->willReturnOnConsecutiveCalls($documentA, null, $documentA, $documentB)
        ;

        $this->assertEquals([], $this->helper->findMany(['/foo', 'bar'], false, false, null, 'Exception'));
        $this->assertEquals([$documentA, $documentB], $this->helper->findMany(['/foo', 'bar'], false, false, null, 'stdClass'));
    }

    public function testFindManyIgnoreRole(): void
    {
        $documentA = new \stdClass();
        $documentB = new \stdClass();

        $this->manager
            ->method('find')
            ->will($this->onConsecutiveCalls($documentA, $documentB))
        ;

        $this->pwc
            ->method('isGranted')
            ->will($this->onConsecutiveCalls(false, true))
        ;

        $this->assertEquals([$documentB], $this->helper->findMany(['/foo', '/bar'], false, false, true));
    }

    public function testFindManyIgnoreWorkflow(): void
    {
        $documentA = new \stdClass();
        $documentB = new \stdClass();

        $this->manager
            ->method('find')
            ->will($this->onConsecutiveCalls($documentA, $documentB))
        ;

        $this->pwc->expects($this->never())
            ->method('isGranted')
        ;

        $this->assertEquals([$documentA, $documentB], $this->helper->findMany(['/foo', '/bar'], false, false, null));
    }

    public function testFindManyLimitOffset(): void
    {
        $documentA = new \stdClass();
        $documentB = new \stdClass();

        $this->manager
            ->method('find')
            ->will($this->onConsecutiveCalls($documentA, $documentB, $documentA, $documentB, $documentA, $documentB))
        ;

        $this->assertEquals([$documentA], $this->helper->findMany(['/foo', 'bar'], 1, false, null));
        $this->assertEquals([$documentB], $this->helper->findMany(['/foo', 'bar'], false, 1, null));
        $this->assertEquals([$documentB], $this->helper->findMany(['/foo', 'bar'], 1, 1, null));
    }

    public function testFindManyNoWorkflow(): void
    {
        $extension = new Cmf(null);
        $extension->setDoctrineRegistry($this->managerRegistry, 'foo');

        $documentA = new \stdClass();

        $this->manager
            ->method('find')
            ->with(null, '/foo')
            ->willReturn($documentA)
        ;

        $this->expectException(InvalidConfigurationException::class);
        $extension->findMany(['/foo', '/bar'], false, false);
    }

    public function testIsPublished(): void
    {
        $this->assertFalse($this->helper->isPublished(null));

        $document = new \stdClass();

        $this->pwc
            ->method('isGranted')
            ->with(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $document)
            ->will($this->onConsecutiveCalls(false, true))
        ;

        $this->assertFalse($this->helper->isPublished($document));
        $this->assertTrue($this->helper->isPublished($document));
    }

    public function testIsPublishedNoWorkflow(): void
    {
        $extension = new Cmf(null);
        $extension->setDoctrineRegistry($this->managerRegistry, 'foo');

        $this->expectException(InvalidConfigurationException::class);
        $extension->isPublished(new \stdClass());
    }

    public function testIsLinkable(): void
    {
        $this->assertFalse($this->helper->isLinkable(null));
        $this->assertFalse($this->helper->isLinkable('a'));
        $this->assertFalse($this->helper->isLinkable($this));

        $content = $this->createMock(RouteReferrersReadInterface::class);
        $content
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn([])
        ;
        $this->assertFalse($this->helper->isLinkable($content));

        $route = $this->createMock(Route::class);
        $content = $this->createMock(RouteReferrersReadInterface::class);
        $content
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn([$route])
        ;
        $this->assertTrue($this->helper->isLinkable($content));
    }

    public function testGetLocalesFor(): void
    {
        $this->assertEquals([], $this->helper->getLocalesFor(null));

        $document = new \stdClass();

        $this->manager
            ->method('find')
            ->with(null, '/foo')
            ->will($this->onConsecutiveCalls(null, $document))
        ;

        $this->assertEquals([], $this->helper->getLocalesFor('/foo'));

        $this->manager->expects($this->once())
            ->method('getLocalesFor')
            ->with($document)
            ->willReturn(['en', 'de'])
        ;

        $this->assertEquals(['en', 'de'], $this->helper->getLocalesFor('/foo'));
    }

    public function testGetLocalesForMissingTranslationException(): void
    {
        $this->markTestIncomplete('TODO: write test');
    }

    public function testGetChild(): void
    {
        $parent = new \stdClass();

        $this->assertNull($this->helper->getChild($parent, 'bar'));

        $child = new \stdClass();

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($parent)
            ->willReturn('/foo')
        ;

        $this->manager->expects($this->once())
            ->method('find')
            ->with(null, '/foo/bar')
            ->willReturn($child)
        ;

        $this->assertEquals($child, $this->helper->getChild($parent, 'bar'));
    }

    public function testGetChildError(): void
    {
        $parent = new \stdClass();

        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($parent)
            ->willThrowException(new \Exception('test'))
        ;

        $this->assertFalse($this->helper->getChild($parent, 'bar'));
    }

    public function testGetChildren(): void
    {
        $this->markTestIncomplete('TODO: write test');
    }

    public function testGetChildrenFilterClass(): void
    {
        $this->markTestIncomplete('TODO: write test');
    }

    public function testGetChildrenIgnoreRole(): void
    {
        $this->markTestIncomplete('TODO: write test');
    }

    public function testGetChildrenLimitOffset(): void
    {
        $this->markTestIncomplete('TODO: write test');
    }

    public function testGetLinkableChildren(): void
    {
        $this->assertEquals([], $this->helper->getLinkableChildren(null));

        $this->markTestIncomplete('TODO: write test');
    }
}
