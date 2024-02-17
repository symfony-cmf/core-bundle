<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;

/**
 * Provides CMF helper functions.
 *
 * @author Wouter J <waldio.webdesign@gmail.com>
 */
class CmfHelper extends Helper
{
    /**
     * @var Cmf
     */
    private $cmf;

    public function __construct(Cmf $cmf)
    {
        $this->cmf = $cmf;
    }

    /**
     * Gets the helper name.
     *
     * @return string
     */
    public function getName()
    {
        return 'cmf';
    }

    /**
     * @param object $document
     *
     * @return bool|string node name or false if the document is not in the unit of work
     */
    public function getNodeName($document)
    {
        return $this->cmf->getNodeName($document);
    }

    /**
     * @param object $document
     *
     * @return bool|string node name or false if the document is not in the unit of work
     */
    public function getParentPath($document)
    {
        return $this->cmf->getParentPath($document);
    }

    /**
     * @param object $document
     *
     * @return bool|string path or false if the document is not in the unit of work
     */
    public function getPath($document)
    {
        return $this->cmf->getPath($document);
    }

    /**
     * Finds a document by path.
     *
     * @return object|null
     */
    public function find($path)
    {
        return $this->cmf->find($path);
    }

    /**
     * Finds a document by path and locale.
     *
     * @param string|object $pathOrDocument the identifier of the class (path or document object)
     * @param string        $locale         the language to try to load
     * @param bool          $fallback       set to true if the language fallback mechanism should be used
     *
     * @return object|null
     */
    public function findTranslation($pathOrDocument, $locale, $fallback = true)
    {
        return $this->cmf->findTranslation($pathOrDocument, $locale, $fallback);
    }

    /**
     * @param array       $paths      list of paths
     * @param int|bool    $limit      int limit or false
     * @param string|bool $offset     string node name to which to skip to or false
     * @param bool|null   $ignoreRole if the role should be ignored or null if publish workflow should be ignored
     * @param string|null $class      class name to filter on
     *
     * @return array
     */
    public function findMany($paths = [], $limit = false, $offset = false, $ignoreRole = false, $class = null)
    {
        return $this->cmf->findMany($paths, $limit, $offset, $ignoreRole, $class);
    }

    /**
     * Check if a document is published, regardless of the current users role.
     *
     * If you need the bypass role, you will have a firewall configured and can
     * simply use {{ is_granted('VIEW', document) }}
     *
     * @param object $document
     *
     * @return bool
     */
    public function isPublished($document)
    {
        return $this->cmf->isPublished($document);
    }

    /**
     * Get the locales of the document.
     *
     * @param string|object $document         Document instance or path
     * @param bool          $includeFallbacks
     *
     * @return array
     */
    public function getLocalesFor($document, $includeFallbacks = false)
    {
        return $this->cmf->getLocalesFor($document, $includeFallbacks);
    }

    /**
     * @param string|object $parent parent path/document
     * @param string        $name
     *
     * @return bool|object|null child or null if the child cannot be found
     *                          or false if the parent is not managed by
     *                          the configured document manager
     */
    public function getChild($parent, $name)
    {
        return $this->cmf->getChild($parent, $name);
    }

    /**
     * Gets child documents.
     *
     * @param string|object $parent     parent id or document
     * @param int|bool      $limit      maximum number of children to get or
     *                                  false for no limit
     * @param string|bool   $offset     node name to which to skip to or false
     * @param string|null   $filter     child name filter (optional)
     * @param bool|null     $ignoreRole whether the role should be ignored or
     *                                  null if publish workflow should be
     *                                  ignored (defaults to false)
     * @param string|null   $class      class name to filter on (optional)
     *
     * @return array
     */
    public function getChildren($parent, $limit = false, $offset = false, $filter = null, $ignoreRole = false, $class = null)
    {
        return $this->cmf->getChildren($parent, $limit, $offset, $filter, $ignoreRole, $class);
    }

    /**
     * Gets linkable child documents of a document or repository id.
     *
     * This has the same semantics as the isLinkable method.
     *
     * @param string|object $parent     parent path/document
     * @param int|bool      $limit      limit or false for no limit
     * @param string|bool   $offset     node name to which to skip to or false
     *                                  to not skip any elements
     * @param string|null   $filter     child name filter
     * @param bool|null     $ignoreRole whether the role should be ignored or
     *                                  null if publish workflow should be
     *                                  ignored (defaults to false)
     * @param string|null   $class      class name to filter on
     *
     * @return array
     *
     * @see isLinkable
     */
    public function getLinkableChildren($parent, $limit = false, $offset = false, $filter = null, $ignoreRole = false, $class = null)
    {
        return $this->cmf->getLinkableChildren($parent, $limit, $offset, $filter, $ignoreRole, $class);
    }

    /**
     * Check whether a document can be linked to, meaning the path() function
     * should be usable.
     *
     * A document is linkable if it is either instance of
     * Symfony\Component\Routing\Route or implements the
     * RouteReferrersReadInterface and actually returns at least one route in
     * getRoutes.
     *
     * This does not work for route names or other things some routers may
     * support, only for objects.
     *
     * @param object $document
     *
     * @return bool true if it is possible to generate a link to $document
     */
    public function isLinkable($document)
    {
        return $this->cmf->isLinkable($document);
    }

    /**
     * @param string|object $parent parent path/document
     * @param int|null      $depth  null denotes no limit, depth of 1 means
     *                              direct children only
     *
     * @return array
     */
    public function getDescendants($parent, $depth = null)
    {
        return $this->cmf->getDescendants($parent, $depth);
    }

    /**
     * Gets the previous document.
     *
     * @param string|object      $current    document instance or path from which to search
     * @param string|object|null $anchor     document instance or path which serves as an anchor from which to flatten the hierarchy
     * @param int|null           $depth      depth up to which to traverse down the tree when an anchor is provided
     * @param bool               $ignoreRole if to ignore the role
     * @param string|null        $class      the class to filter by
     *
     * @return object|null
     */
    public function getPrev($current, $anchor = null, $depth = null, $ignoreRole = false, $class = null)
    {
        return $this->cmf->getPrev($current, $anchor, $depth, $ignoreRole, $class);
    }

    /**
     * Gets the next document.
     *
     * @param string|object      $current    document instance or path from which to search
     * @param string|object|null $anchor     document instance or path which serves as an anchor from which to flatten the hierarchy
     * @param int|null           $depth      depth up to which to traverse down the tree when an anchor is provided
     * @param bool               $ignoreRole if to ignore the role
     * @param string|null        $class      the class to filter by
     *
     * @return object|null
     */
    public function getNext($current, $anchor = null, $depth = null, $ignoreRole = false, $class = null)
    {
        return $this->cmf->getNext($current, $anchor, $depth, $ignoreRole, $class);
    }

    /**
     * Gets the previous linkable document.
     *
     * This has the same semantics as the isLinkable method.
     *
     * @param string|object      $current    Document instance or path from
     *                                       which to search
     * @param string|object|null $anchor     Document instance or path which
     *                                       serves as an anchor from which to
     *                                       flatten the hierarchy
     * @param int|null           $depth      Depth up to which to traverse down
     *                                       the tree when an anchor is
     *                                       provided
     * @param bool               $ignoreRole Whether to ignore the role,
     *
     * @return object|null
     *
     * @see isLinkable
     */
    public function getPrevLinkable($current, $anchor = null, $depth = null, $ignoreRole = false)
    {
        return $this->cmf->getPrevLinkable($current, $anchor, $depth, $ignoreRole);
    }

    /**
     * Gets the next linkable document.
     *
     * This has the same semantics as the isLinkable method.
     *
     * @param string|object      $current    Document instance or path from
     *                                       which to search
     * @param string|object|null $anchor     Document instance or path which
     *                                       serves as an anchor from which to
     *                                       flatten the hierarchy
     * @param int|null           $depth      Depth up to which to traverse down
     *                                       the tree when an anchor is
     *                                       provided
     * @param bool               $ignoreRole Whether to ignore the role
     *
     * @return object|null
     *
     * @see isLinkable
     */
    public function getNextLinkable($current, $anchor = null, $depth = null, $ignoreRole = false)
    {
        return $this->cmf->getNextLinkable($current, $anchor, $depth, $ignoreRole);
    }
}
