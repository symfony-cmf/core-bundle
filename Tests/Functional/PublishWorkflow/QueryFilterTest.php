<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\Form;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Symfony\Cmf\Bundle\CoreBundle\Doctrine\Phpcr\PublishWorkflowQueryFilter;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class QueryFilterTest extends BaseTestCase
{
    /**
     * @var QueryBuilder
     */
    private $qb;

    private $documentClass = 'Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document\Publishable';

    public function setUp()
    {
        $this->db('PHPCR')->loadFixtures(array('\Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\DataFixture\LoadPublishableData'));

        /** @var DocumentManagerInterface $dm */
        $dm = $this->db('PHPCR')->getOm();
        $this->qb = $dm->createQueryBuilder();
        $this->qb->fromDocument($this->documentClass, 'a');
        $documents = $this->qb->getQuery()->getResult();
        $this->assertCount(3, $documents);

        $this->assertTrue(isset($documents['/published']));
        $this->assertTrue(isset($documents['/unpublishable']));
        $this->assertTrue(isset($documents['/timeperiod']));
    }

    public function testQueryFilter()
    {
        $filter = new PublishWorkflowQueryFilter();
//        $filter->filterQuery($this->qb);
        $this->qb
            ->where()
            ->lte()
            ->field('a.publishStartDate')
            ->literal(new \DateTime());
var_dump($this->qb->getQuery()->getStatement());
        $documents = $this->qb->getQuery()->getResult();
        $this->assertCount(1, $documents);
var_dump(array_keys($documents->toArray()));
        $this->assertTrue(isset($documents['/published']));
    }

    public function testSkip()
    {
        $filter = new PublishWorkflowQueryFilter();
        $filter->skipClass($this->documentClass);
        $filter->filterQuery($this->qb);
        $documents = $this->qb->getQuery()->getResult();
        $this->assertCount(3, $documents);

        $this->assertTrue(isset($documents['/published']));
        $this->assertTrue(isset($documents['/unpublishable']));
        $this->assertTrue(isset($documents['/timeperiod']));
    }

    /**
     * This test is relying on the fact that publish start date may be null
     */
    public function testMap()
    {
        $filter = new PublishWorkflowQueryFilter();
        $filter->configureFields($this->documentClass, array('publishStartDate' => 'foo'));
        $filter->filterQuery($this->qb);
        $documents = $this->qb->getQuery()->getResult();
        $this->assertCount(2, $documents);

        $this->assertTrue(isset($documents['/published']));
        $this->assertTrue(isset($documents['/timeperiod']));
    }
}
