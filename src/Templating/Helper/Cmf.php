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

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Translation\MissingTranslationException;
use Doctrine\Persistence\ManagerRegistry;
use PHPCR\Util\PathHelper;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Layout helper for the CMF.
 *
 * This class is used by the twig extension.
 *
 * @author Wouter J <waldio.webdesign@gmail.com>
 */
class Cmf
{
    private ManagerRegistry $doctrineRegistry;
    private ?string $doctrineManagerName;

    protected DocumentManager $dm;

    public function __construct(
        private ?AuthorizationCheckerInterface $publishWorkflowChecker = null
    ) {
    }

    /**
     * Set the doctrine manager registry to fetch the object manager from.
     *
     * @param string|null $managerName Manager name if not the default
     */
    public function setDoctrineRegistry(ManagerRegistry $registry, ?string $managerName = null): void
    {
        if (isset($this->doctrineRegistry)) {
            throw new \LogicException('Do not call this setter repeatedly.');
        }

        $this->doctrineRegistry = $registry;
        $this->doctrineManagerName = $managerName;
    }

    protected function getDm(): DocumentManager
    {
        if (!isset($this->dm)) {
            if (!isset($this->doctrineRegistry)) {
                throw new \RuntimeException('Doctrine is not available.');
            }

            $this->dm = $this->doctrineRegistry->getManager($this->doctrineManagerName);
        }

        return $this->dm;
    }

    /**
     * @param object $document
     *
     * @return bool|string node name or false if the document is not in the unit of work
     */
    public function getNodeName($document): bool|string
    {
        $path = $this->getPath($document);
        if (false === $path) {
            return false;
        }

        return PathHelper::getNodeName($path);
    }

    /**
     * @param object $document
     *
     * @return bool|string node name or false if the document is not in the unit of work
     */
    public function getParentPath($document): bool|string
    {
        $path = $this->getPath($document);
        if (!$path) {
            return false;
        }

        return PathHelper::getParentPath($path);
    }

    /**
     * @param object $document
     *
     * @return bool|string path or false if the document is not in the unit of work
     */
    public function getPath($document): bool|string
    {
        try {
            return $this->getDm()->getUnitOfWork()->getDocumentId($document);
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Finds a document by path.
     */
    public function find($path): ?object
    {
        return $this->getDm()->find(null, $path);
    }

    /**
     * Finds a document by path and locale.
     *
     * @param string|object $pathOrDocument the identifier of the class (path or document object)
     * @param string        $locale         the language to try to load
     * @param bool          $fallback       set to true if the language fallback mechanism should be used
     */
    public function findTranslation($pathOrDocument, string $locale, bool $fallback = true): ?object
    {
        if (\is_object($pathOrDocument)) {
            $path = $this->getDm()->getUnitOfWork()->getDocumentId($pathOrDocument);
        } else {
            $path = $pathOrDocument;
        }

        return $this->getDm()->findTranslation(null, $path, $locale, $fallback);
    }

    /**
     * Gets a document instance and validate if its eligible.
     *
     * @param string|object $document   the id of a document or the document
     *                                  object itself
     * @param bool|null     $ignoreRole whether the bypass role should be
     *                                  ignored (leading to only show published content regardless of the
     *                                  current user) or null to skip the published check completely
     * @param string|null   $class      class name to filter on
     *
     * @return object|null
     */
    private function getDocument($document, ?bool $ignoreRole = false, ?string $class = null): object|string|null
    {
        if (\is_string($document)) {
            try {
                $document = $this->getDm()->find(null, $document);
            } catch (MissingTranslationException $e) {
                return null;
            }
        }

        if (null !== $ignoreRole && null === $this->publishWorkflowChecker) {
            throw new InvalidConfigurationException('You can not fetch only published documents when the publishWorkflowChecker is not set. Either enable the publish workflow or pass "ignoreRole = null" to skip publication checks.');
        }

        if (empty($document)
            || (null !== $class && !($document instanceof $class))
            || (false === $ignoreRole && !$this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $document))
            || (true === $ignoreRole && !$this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $document))
        ) {
            return null;
        }

        return $document;
    }

    /**
     * @param string|false $offset     string node name to which to skip to or false to start from the beginning
     * @param bool|null    $ignoreRole if the role should be ignored or null if publish workflow should be ignored
     * @param string|null  $class      class name to filter on
     */
    public function findMany(array $paths = [], int|false $limit = false, string|false $offset = false, ?bool $ignoreRole = false, ?string $class = null): array
    {
        if ($offset) {
            $paths = \array_slice($paths, $offset);
        }

        $result = [];
        foreach ($paths as $path) {
            $document = $this->getDocument($path, $ignoreRole, $class);
            if (null === $document) {
                continue;
            }

            $result[] = $document;
            if (false !== $limit) {
                --$limit;
                if (!$limit) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Check if a document is published, regardless of the current users role.
     *
     * If you need the bypass role, you will have a firewall configured and can
     * simply use {{ is_granted('VIEW', document) }}
     */
    public function isPublished(?object $document): bool
    {
        if (null === $this->publishWorkflowChecker) {
            throw new InvalidConfigurationException('You can not check for publication as the publish workflow is not enabled.');
        }

        if (null === $document) {
            return false;
        }

        return $this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $document);
    }

    /**
     * Get the locales of the document.
     *
     * @return string[]
     */
    public function getLocalesFor(object|string|null $document, bool $includeFallbacks = false): array
    {
        if (\is_string($document)) {
            $document = $this->getDm()->find(null, $document);
        }

        if (null === $document) {
            return [];
        }

        try {
            $locales = $this->getDm()->getLocalesFor($document, $includeFallbacks);
        } catch (MissingTranslationException $e) {
            $locales = [];
        }

        return $locales;
    }

    /**
     * @param object|string $parent parent path/document
     *
     * @return bool|object|null child or null if the child cannot be found
     *                          or false if the parent is not managed by
     *                          the configured document manager
     */
    public function getChild(object|string $parent, string $name): object|bool|null
    {
        if (\is_object($parent)) {
            try {
                $parent = $this->getDm()->getUnitOfWork()->getDocumentId($parent);
            } catch (\Exception) {
                return false;
            }
        }

        return $this->getDm()->find(null, "$parent/$name");
    }

    /**
     * Gets child documents.
     *
     * @param int|false    $limit      maximum number of children to get or
     *                                 false for no limit
     * @param string|false $offset     node name to which to skip to or false
     * @param string|null  $filter     child name filter (optional)
     * @param bool|null    $ignoreRole whether the role should be ignored or
     *                                 null if publish workflow should be
     *                                 ignored (defaults to false)
     * @param string|null  $class      class name to filter on (optional)
     */
    public function getChildren(object|string|null $parent, int|false $limit = false, string|false $offset = false, ?string $filter = null, ?bool $ignoreRole = false, ?string $class = null): array
    {
        if (empty($parent)) {
            return [];
        }

        if (\is_object($parent)) {
            $parent = $this->getDm()->getUnitOfWork()->getDocumentId($parent);
        }
        $node = $this->getDm()->getPhpcrSession()->getNode($parent);
        $children = (array) $node->getNodeNames();
        foreach ($children as $key => $child) {
            // filter before fetching data already to save some traffic
            if (str_starts_with($child, 'phpcr_locale:')) {
                unset($children[$key]);

                continue;
            }
            $children[$key] = "$parent/$child";
        }
        if ($offset) {
            $key = array_search($offset, $children, true);
            if (false === $key) {
                return [];
            }
            $children = \array_slice($children, $key);
        }

        $result = [];
        foreach ($children as $name => $child) {
            // if we requested all children above, we did not filter yet
            if (str_starts_with($name, 'phpcr_locale:')) {
                continue;
            }

            // $child is already a document, but this method also checks access
            $child = $this->getDocument($child, $ignoreRole, $class);
            if (null === $child) {
                continue;
            }

            $result[] = $child;
            if (false !== $limit) {
                --$limit;
                if (!$limit) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Gets linkable child documents of a document or repository id.
     *
     * This has the same semantics as the isLinkable method.
     *
     * @param int|false    $limit      limit or false for no limit
     * @param string|false $offset     node name to which to skip to or false
     *                                 to not skip any elements
     * @param string|null  $filter     child name filter
     * @param bool|null    $ignoreRole whether the role should be ignored or
     *                                 null if publish workflow should be
     *                                 ignored (defaults to false)
     * @param string|null  $class      class name to filter on
     *
     * @see isLinkable
     */
    public function getLinkableChildren(object|string|null $parent, int|false $limit = false, string|false $offset = false, ?string $filter = null, ?bool $ignoreRole = false, ?string $class = null): array
    {
        $children = $this->getChildren($parent, $limit, $offset, $filter, $ignoreRole, $class);
        foreach ($children as $key => $value) {
            if (!$this->isLinkable($value)) {
                unset($children[$key]);
            }
        }

        return $children;
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
     * @return bool true if it is possible to generate a link to $document
     */
    public function isLinkable(mixed $document): bool
    {
        return
            $document instanceof Route
            || ($document instanceof RouteReferrersReadInterface
                && \count($document->getRoutes()) > 0
            )
        ;
    }

    /**
     * Gets the paths of children, updating the $children parameter.
     *
     * @param string[] $children
     * @param ?int     $depth
     */
    private function getChildrenPaths(?string $path, array &$children, ?int $depth): void
    {
        if (null === $path) {
            return;
        }
        if ((null !== $depth) && $depth-- < 1) {
            return;
        }

        $node = $this->getDm()->getPhpcrSession()->getNode($path);
        $names = (array) $node->getNodeNames();
        foreach ($names as $name) {
            if (str_starts_with($name, 'phpcr_locale:')) {
                continue;
            }

            $children[] = $child = "$path/$name";
            $this->getChildrenPaths($child, $children, $depth);
        }
    }

    /**
     * @param int|null $depth null denotes no limit, depth of 1 means
     *                        direct children only
     *
     * @return string[]
     */
    public function getDescendants(object|string|null $parent, ?int $depth = null): array
    {
        if ('' === $parent) {
            return [];
        }

        $children = [];
        if (\is_object($parent)) {
            $parent = $this->getDm()->getUnitOfWork()->getDocumentId($parent);
        }
        $this->getChildrenPaths($parent, $children, $depth);

        return $children;
    }

    /**
     * Check children for a possible following document.
     */
    private function checkChildren(array $childNames, string $path, ?bool $ignoreRole = false, ?string $class = null): object|null
    {
        foreach ($childNames as $name) {
            if (str_starts_with($name, 'phpcr_locale:')) {
                continue;
            }

            $child = $this->getDocument(ltrim($path, '/')."/$name", $ignoreRole, $class);

            if ($child) {
                return $child;
            }
        }

        return null;
    }

    /**
     * Traverse the depth to find previous documents.
     */
    private function traversePrevDepth(?int $depth, int $anchorDepth, array $childNames, string $path, bool $ignoreRole, ?string $class): object|null
    {
        foreach ($childNames as $childName) {
            $childPath = "$path/$childName";
            $node = $this->getDm()->getPhpcrSession()->getNode($childPath);
            if (null === $depth || PathHelper::getPathDepth($childPath) - $anchorDepth < $depth) {
                $childNames = $node->getNodeNames()->getArrayCopy();
                if (!empty($childNames)) {
                    $childNames = array_reverse($childNames);
                    $result = $this->traversePrevDepth($depth, $anchorDepth, $childNames, $childPath, $ignoreRole, $class);
                    if ($result) {
                        return $result;
                    }
                }
            }

            $result = $this->checkChildren($childNames, $node->getPath(), $ignoreRole, $class);
            if ($result) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Search for a previous document.
     *
     * @param object|string $path       document instance or path from which to search
     * @param object|string $anchor     document instance or path which serves as an anchor from which to flatten the hierarchy
     * @param int|null      $depth      depth up to which to traverse down the tree when an anchor is provided
     * @param bool          $ignoreRole if to ignore the role
     * @param string|null   $class      the class to filter by
     */
    private function searchDepthPrev(object|string $path, object|string $anchor, ?int $depth = null, ?bool $ignoreRole = false, ?string $class = null): object|null
    {
        if (\is_object($path)) {
            $path = $this->getDm()->getUnitOfWork()->getDocumentId($path);
        }

        if (null === $path || '/' === $path) {
            return null;
        }

        $node = $this->getDm()->getPhpcrSession()->getNode($path);

        if (\is_object($anchor)) {
            $anchor = $this->getDm()->getUnitOfWork()->getDocumentId($anchor);
        }

        if (!str_starts_with($path, $anchor)) {
            throw new \RuntimeException("The anchor path '$anchor' is not a parent of the current path '$path'.");
        }

        if ($path === $anchor) {
            return null;
        }

        $parent = $node->getParent();
        $parentPath = $parent->getPath();

        $childNames = $parent->getNodeNames()->getArrayCopy();
        if (!empty($childNames)) {
            $childNames = array_reverse($childNames);
            $key = array_search($node->getName(), $childNames, true);
            $childNames = \array_slice($childNames, $key + 1);

            if (!empty($childNames)) {
                // traverse the previous siblings down the tree
                $result = $this->traversePrevDepth($depth, PathHelper::getPathDepth($anchor), $childNames, $parentPath, $ignoreRole, $class);
                if ($result) {
                    return $result;
                }

                // check siblings
                $result = $this->checkChildren($childNames, $parentPath, $ignoreRole, $class);
                if ($result) {
                    return $result;
                }
            }
        }

        // check parents
        if (str_starts_with($parentPath, $anchor)) {
            $parent = $parent->getParent();
            $childNames = $parent->getNodeNames()->getArrayCopy();
            $key = array_search(PathHelper::getNodeName($parentPath), $childNames, true);
            $childNames = \array_slice($childNames, 0, $key + 1);
            $childNames = array_reverse($childNames);
            if (!empty($childNames)) {
                $result = $this->checkChildren($childNames, $parent->getPath(), $ignoreRole, $class);
                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Search for a next document.
     *
     * @param object|string $path       document instance or path from which to search
     * @param object|string $anchor     document instance or path which serves as an anchor from which to flatten the hierarchy
     * @param int|null      $depth      depth up to which to traverse down the tree when an anchor is provided
     * @param bool          $ignoreRole if to ignore the role
     * @param string|null   $class      the class to filter by
     */
    private function searchDepthNext(object|string $path, object|string $anchor, ?int $depth = null, ?bool $ignoreRole = false, ?string $class = null): object|null
    {
        if (\is_object($path)) {
            $path = $this->getDm()->getUnitOfWork()->getDocumentId($path);
        }

        if (null === $path || '/' === $path) {
            return null;
        }

        $node = $this->getDm()->getPhpcrSession()->getNode($path);

        if (\is_object($anchor)) {
            $anchor = $this->getDm()->getUnitOfWork()->getDocumentId($anchor);
        }

        if (!str_starts_with($path, $anchor)) {
            throw new \RuntimeException("The anchor path '$anchor' is not a parent of the current path '$path'.");
        }

        // take the first eligible child if there are any
        if (null === $depth || PathHelper::getPathDepth($path) - PathHelper::getPathDepth($anchor) < $depth) {
            $childNames = $node->getNodeNames()->getArrayCopy();
            $result = $this->checkChildren($childNames, $path, $ignoreRole, $class);
            if ($result) {
                return $result;
            }
        }

        $parent = $node->getParent();
        $parentPath = PathHelper::getParentPath($path);

        // take the first eligible sibling
        if (str_starts_with($parentPath, $anchor)) {
            $childNames = $parent->getNodeNames()->getArrayCopy();
            $key = array_search($node->getName(), $childNames, true);
            $childNames = \array_slice($childNames, $key + 1);
            $result = $this->checkChildren($childNames, $parentPath, $ignoreRole, $class);
            if ($result) {
                return $result;
            }
        }

        // take the first eligible parent, traverse up
        while ('/' !== $parentPath) {
            $parent = $parent->getParent();
            if (!str_contains($parent->getPath(), $anchor)) {
                return null;
            }

            $childNames = $parent->getNodeNames()->getArrayCopy();
            $key = array_search(PathHelper::getNodeName($parentPath), $childNames, true);
            $childNames = \array_slice($childNames, $key + 1);
            $parentPath = $parent->getPath();
            $result = $this->checkChildren($childNames, $parentPath, $ignoreRole, $class);
            if ($result) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Search for a related document.
     *
     * @param string|object $path       document instance or path from which to search
     * @param bool          $reverse    if to traverse back
     * @param bool          $ignoreRole if to ignore the role
     * @param string|null   $class      the class to filter by
     */
    private function search($path, ?bool $reverse = false, ?bool $ignoreRole = false, ?string $class = null): object|null
    {
        if (\is_object($path)) {
            $path = $this->getDm()->getUnitOfWork()->getDocumentId($path);
        }

        if (null === $path || '/' === $path) {
            return null;
        }

        $node = $this->getDm()->getPhpcrSession()->getNode($path);
        $parentNode = $node->getParent();
        $childNames = $parentNode->getNodeNames()->getArrayCopy();
        if ($reverse) {
            $childNames = array_reverse($childNames);
        }

        $key = array_search($node->getName(), $childNames, true);
        $childNames = \array_slice($childNames, $key + 1);

        return $this->checkChildren($childNames, $parentNode->getPath(), $ignoreRole, $class);
    }

    /**
     * Gets the previous document.
     *
     * @param object|string|null $current    document instance or path from which to search
     * @param object|string|null $anchor     document instance or path which serves as an anchor from which to flatten the hierarchy
     * @param int|null           $depth      depth up to which to traverse down the tree when an anchor is provided
     * @param bool               $ignoreRole if to ignore the role
     * @param string|null        $class      the class to filter by
     */
    public function getPrev(object|string|null $current, object|string|null $anchor = null, ?int $depth = null, ?bool $ignoreRole = false, ?string $class = null): object|null
    {
        if ($anchor) {
            return $this->searchDepthPrev($current, $anchor, $depth, $ignoreRole, $class);
        }

        return $this->search($current, true, $ignoreRole, $class);
    }

    /**
     * Gets the next document.
     *
     * @param object|string|null $current    document instance or path from which to search
     * @param object|string|null $anchor     document instance or path which serves as an anchor from which to flatten the hierarchy
     * @param int|null           $depth      depth up to which to traverse down the tree when an anchor is provided
     * @param bool               $ignoreRole if to ignore the role
     * @param string|null        $class      the class to filter by
     */
    public function getNext(object|string|null $current, object|string|null $anchor = null, ?int $depth = null, ?bool $ignoreRole = false, ?string $class = null): object|null
    {
        if ($anchor) {
            return $this->searchDepthNext($current, $anchor, $depth, $ignoreRole, $class);
        }

        return $this->search($current, false, $ignoreRole, $class);
    }

    /**
     * Gets the previous linkable document.
     *
     * This has the same semantics as the isLinkable method.
     *
     * @param object|string|null $current    Document instance or path from
     *                                       which to search
     * @param object|string|null $anchor     Document instance or path which
     *                                       serves as an anchor from which to
     *                                       flatten the hierarchy
     * @param int|null           $depth      Depth up to which to traverse down
     *                                       the tree when an anchor is
     *                                       provided
     * @param bool               $ignoreRole Whether to ignore the role,
     *
     * @see isLinkable
     */
    public function getPrevLinkable(object|string|null $current, object|string $anchor = null, ?int $depth = null, ?bool $ignoreRole = false): object|null
    {
        while ($candidate = $this->getPrev($current, $anchor, $depth, $ignoreRole)) {
            if ($this->isLinkable($candidate)) {
                return $candidate;
            }

            $current = $candidate;
        }

        return null;
    }

    /**
     * Gets the next linkable document.
     *
     * This has the same semantics as the isLinkable method.
     *
     * @param object|string|null $current    Document instance or path from
     *                                       which to search
     * @param object|string|null $anchor     Document instance or path which
     *                                       serves as an anchor from which to
     *                                       flatten the hierarchy
     * @param int|null           $depth      Depth up to which to traverse down
     *                                       the tree when an anchor is
     *                                       provided
     * @param bool               $ignoreRole Whether to ignore the role
     *
     * @see isLinkable
     */
    public function getNextLinkable(object|string|null $current, object|string|null $anchor = null, ?int $depth = null, ?bool $ignoreRole = false): object|null
    {
        while ($candidate = $this->getNext($current, $anchor, $depth, $ignoreRole)) {
            if ($this->isLinkable($candidate)) {
                return $candidate;
            }

            $current = $candidate;
        }

        return null;
    }
}
