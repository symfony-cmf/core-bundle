<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Migrator;

use Doctrine\Bundle\PHPCRBundle\Migrator\AbstractMigrator;

use PHPCR\ItemInterface;
use PHPCR\NodeInterface;

use PHPCR\Util\TreeWalker;

class RouteCompilerClassMigrator extends AbstractMigrator
{
    /**
     * @var int
     */
    private $count = 0;

    public function migrate($identifer = '/', $depth = -1)
    {
        $walker = new TreeWalker($this);
        $node = $this->session->getNodeByIdentifier($identifer);
        $walker->traverse($node, $depth);
        $this->session->save();

        $this->output->write('Updated '.$this->count.' routes', true);

        return 0;
    }

    protected function entering(ItemInterface $item, $depth)
    {
        if ($item instanceof NodeInterface) {
            if ($item->hasProperty('phpcr:class')
                && (is_subclass_of($item->getPropertyValue('phpcr:class'), 'Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route')
                    || 'Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route' === $item->getPropertyValue('phpcr:class')
                )
            ) {
                $options = $item->hasProperty('options') ? $item->getPropertyValue('options') : array();
                $optionKeys = $item->hasProperty('optionKeys') ? $item->getPropertyValue('optionKeys') : array();

                if (!in_array('Symfony\Component\Routing\RouteCompiler', $options)) {
                    $options[] = 'Symfony\Component\Routing\RouteCompiler';
                    $optionKeys[] = 'compiler_class';

                    $item->setProperty('options', $options);
                    $item->setProperty('optionKeys', $optionKeys);
                    $this->output->write("Path: ".$item->getPath(), true);
                    $this->count++;
                }
            }
        }
    }

    protected function leaving(ItemInterface $item, $depth)
    {
    }
}
