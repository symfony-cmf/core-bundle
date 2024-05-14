<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Doctrine\Phpcr;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\ODM\PHPCR\Query\Builder\AbstractNode;
use Doctrine\ODM\PHPCR\Query\Builder\From;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Query\Builder\SourceDocument;
use Doctrine\ODM\PHPCR\Query\Builder\SourceJoin;
use Doctrine\ODM\PHPCR\Query\Query;
use PHPCR\Query\QueryInterface;

/**
 * Tool to filter queries to only find published documents.
 *
 * This only takes into account the "publishable" and "time period" voters.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class PublishWorkflowQueryFilter
{
    private $skipClasses = array();

    /**
     * Hashmap of FQN => field configuration as per configureFields().
     *
     * @var array
     */
    private $fieldMap = array();

    /**
     * Mark a class to be skipped from filtering
     *
     * @param string $class FQN
     */
    public function skipClass($class)
    {
        $this->skipClasses[$class] = true;
    }

    /**
     * The default map if not set for a specific class is
     *
     * publishable => publishable
     * publishStartDate => publishStartDate
     * publishEndDate => publishEndDate
     *
     * @param string $class FQN
     * @param array  $map   Hashmap with the field names to use for filtering
     */
    public function configureFields($class, array $map)
    {
        $this->fieldMap[$class] = $map;
    }

    /**
     * Update query to limit results to published documents.
     *
     * @param QueryBuilder $queryBuilder
     */
    public function filterQuery(QueryBuilder $queryBuilder)
    {
        /** @var From $from */
        $from = $queryBuilder->getChildOfType(AbstractNode::NT_FROM);
        $map = $this->extractAlias($from);
        foreach ($map as $alias => $options) {
            if (isset($this->skipClasses[$options['class']])) {
                continue;
            }

            if ($options['publishable']) {
                $queryBuilder
                    ->andWhere()
                        ->eq()
                            ->field($this->getFieldName('publishable', $alias, $options))
                            ->literal(true)
                ;
            }

            if ($options['publish_time_period']) {
                $queryBuilder
                    ->andWhere()
                    ->andX()
                        ->orX()
                            // TODO how to check for IS NULL?
                            ->not()->fieldIsset($this->getFieldName('publishStartDate', $alias, $options))->end()
                            ->lte()
                                ->field($this->getFieldName('publishStartDate', $alias, $options))
                                ->literal(new \DateTime())
                            ->end()
                        ->end()
                    ->andX()
                        ->orX()
                            ->not()->fieldIsset($this->getFieldName('publishEndDate', $alias, $options))->end()
                            ->gte()
                                ->field($this->getFieldName('publishEndDate', $alias, $options))
                                ->literal(new \DateTime())
                            ->end()
                        ->end()
                    ->end()
                ;
            }
        }
    }

    private function extractAlias(AbstractNode $source)
    {
        if ($source instanceof SourceDocument) {
            $class = $source->getDocumentFqn();
            return array(
                $source->getAlias() => array(
                    'class' => $class,
                    'publishable' => is_subclass_of($class, 'Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableReadInterface'),
                    'publish_time_period' => is_subclass_of($class, 'Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodReadInterface'),
                )
            );
        }

        if ($source instanceof From) {
            return $this->extractAlias($source->getChildOfType(AbstractNode::NT_SOURCE));
        }

        if (!$source instanceof SourceJoin) {
            throw new \Exception(sprintf('Source of class %s is not implemented', get_class($source)));
        }

        $map = $this->extractAlias($source->getChildOfType(AbstractNode::NT_SOURCE_JOIN_LEFT));
        return array_merge(
            $map,
            $this->extractAlias($source->getChildOfType(AbstractNode::NT_SOURCE_JOIN_RIGHT))
        );
    }

    private function getFieldName($field, $alias, $options)
    {
        return $alias.'.'.(isset($this->fieldMap[$options['class']][$field])
            ? $this->fieldMap[$options['class']][$field]
            : $field
        );
    }
}
