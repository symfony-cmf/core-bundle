<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Twig\Extension;

use Symfony\Cmf\Bundle\CoreBundle\Templating\Helper\Cmf;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CmfExtension extends AbstractExtension
{
    public function __construct(
        private Cmf $cmf
    ) {
    }

    /**
     * Get list of available functions.
     */
    public function getFunctions(): array
    {
        $functions = [
            new TwigFunction('cmf_is_published', [$this, 'isPublished']),
            new TwigFunction('cmf_child', [$this, 'getChild']),
            new TwigFunction('cmf_children', [$this, 'getChildren']),
            new TwigFunction('cmf_prev', [$this, 'getPrev']),
            new TwigFunction('cmf_next', [$this, 'getNext']),
            new TwigFunction('cmf_find', [$this, 'find']),
            new TwigFunction('cmf_find_translation', [$this, 'findTranslation']),
            new TwigFunction('cmf_find_many', [$this, 'findMany']),
            new TwigFunction('cmf_descendants', [$this, 'getDescendants']),
            new TwigFunction('cmf_nodename', [$this, 'getNodeName']),
            new TwigFunction('cmf_parent_path', [$this, 'getParentPath']),
            new TwigFunction('cmf_path', [$this, 'getPath']),
            new TwigFunction('cmf_document_locales', [$this, 'getLocalesFor']),
        ];

        if (interface_exists(RouteReferrersReadInterface::class)) {
            $functions = array_merge($functions, [
                new TwigFunction('cmf_is_linkable', [$this, 'isLinkable']),
                new TwigFunction('cmf_prev_linkable', [$this, 'getPrevLinkable']),
                new TwigFunction('cmf_next_linkable', [$this, 'getNextLinkable']),
                new TwigFunction('cmf_linkable_children', [$this, 'getLinkableChildren']),
            ]);
        }

        return $functions;
    }

    public function isPublished($document): bool
    {
        return $this->cmf->isPublished($document);
    }

    public function isLinkable($document): bool
    {
        return $this->cmf->isLinkable($document);
    }

    public function getChild($parent, $name): object|bool|null
    {
        return $this->cmf->getChild($parent, $name);
    }

    public function getChildren($parent, $limit = false, $offset = false, $filter = null, $ignoreRole = false, $class = null): array
    {
        return $this->cmf->getChildren($parent, $limit, $offset, $filter, $ignoreRole, $class);
    }

    public function getPrev($current, $anchor = null, $depth = null, $ignoreRole = false, $class = null): object|string|null
    {
        return $this->cmf->getPrev($current, $anchor, $depth, $ignoreRole, $class);
    }

    public function getNext($current, $anchor = null, $depth = null, $ignoreRole = false, $class = null): object|string|null
    {
        return $this->cmf->getNext($current, $anchor, $depth, $ignoreRole, $class);
    }

    public function find($path)
    {
        return $this->cmf->find($path);
    }

    public function findTranslation($path, $locale, $fallback = true)
    {
        return $this->cmf->findTranslation($path, $locale, $fallback);
    }

    public function findMany($paths = [], $limit = false, $offset = false, $ignoreRole = false, $class = null): array
    {
        return $this->cmf->findMany($paths, $limit, $offset, $ignoreRole, $class);
    }

    public function getDescendants($parent, $depth = null): array
    {
        return $this->cmf->getDescendants($parent, $depth);
    }

    public function getNodeName($document): bool|string
    {
        return $this->cmf->getNodeName($document);
    }

    public function getParentPath($document): bool|string
    {
        return $this->cmf->getParentPath($document);
    }

    public function getPath($document): bool|string
    {
        return $this->cmf->getPath($document);
    }

    public function getLocalesFor($document, $includeFallbacks = false): array
    {
        return $this->cmf->getLocalesFor($document, $includeFallbacks);
    }

    public function getPrevLinkable($current, $anchor = null, $depth = null, $ignoreRole = false): object|string|null
    {
        return $this->cmf->getPrevLinkable($current, $anchor, $depth, $ignoreRole);
    }

    public function getNextLinkable($current, $anchor = null, $depth = null, $ignoreRole = false): object|string|null
    {
        return $this->cmf->getNextLinkable($current, $anchor, $depth, $ignoreRole);
    }

    public function getLinkableChildren($parent, $limit = false, $offset = false, $filter = null, $ignoreRole = false, $class = null): array
    {
        return $this->cmf->getLinkableChildren($parent, $limit, $offset, $filter, $ignoreRole, $class);
    }

    public function getName(): string
    {
        return 'cmf';
    }
}
