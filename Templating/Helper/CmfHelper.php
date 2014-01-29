<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\CoreBundle\Templating\Helper;

use PHPCR\Util\PathHelper;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\PHPCR\Translation\MissingTranslationException;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;

/**
 * Provides CMF helper functions.
 *
 * @author Wouter J <waldio.webdesign@gmail.com>
 */
class CmfHelper extends Helper
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var SecurityContextInterface
     */
    protected $publishWorkflowChecker;

    /**
     * Instantiates the content controller.
     *
     * @param SecurityContextInterface $publishWorkflowChecker
     * @param ManagerRegistry          $registry
     * @param string                   $objectManagerName
     */
    public function __construct(SecurityContextInterface $publishWorkflowChecker = null, $registry = null, $objectManagerName = null)
    {
        $this->publishWorkflowChecker = $publishWorkflowChecker;

        if ($registry && $registry instanceof ManagerRegistry) {
            $this->dm = $registry->getManager($objectManagerName);
        }
    }

    protected function getDm()
    {
        if (!$this->dm) {
            throw new \RuntimeException('Document Manager has not been initialized yet.');
        }

        return $this->dm;
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
     * @param  object         $document
     * @return boolean|string node name or false if the document is not in the unit of work
     */
    public function getNodeName($document)
    {
        return PathHelper::getNodeName($this->getPath($document));
    }

    /**
     * @param  object         $document
     * @return boolean|string node name or false if the document is not in the unit of work
     */
    public function getParentPath($document)
    {
        return PathHelper::getParentPath($this->getPath($document));
    }

    /**
     * @param  object         $document
     * @return boolean|string path or false if the document is not in the unit of work
     */
    public function getPath($document)
    {
        try {
            return $this->getDm()->getUnitOfWork()->getDocumentId($document);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Finds a document by path.
     *
     * @param $path
     * @return null|object
     */
    public function find($path)
    {
        return $this->getDm()->find(null, $path);
    }

    /**
     * Gets a document instance and validate if its eligible.
     *
     * @param string|object $document the id of a document or the document
     *      object itself
     * @param boolean|null $ignoreRole whether the bypass role should be
     *      ignored (leading to only show published content regardless of the
     *      current user) or null to skip the published check completely.
     * @param null|string $class class name to filter on
     *
     * @return null|object
     */
    private function getDocument($document, $ignoreRole = false, $class = null)
    {
        if (is_string($document)) {
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
            || (false === $ignoreRole && !$this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ATTRIBUTE, $document))
            || (true === $ignoreRole && !$this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $document))
            || (null != $class && !($document instanceof $class))
        ) {
            return null;
        }

        return $document;
    }

    /**
     * @param array          $paths      list of paths
     * @param int|Boolean    $limit      int limit or false
     * @param string|Boolean $offset     string node name to which to skip to or false
     * @param Boolean|null   $ignoreRole if the role should be ignored or null if publish workflow should be ignored
     * @param null|string    $class      class name to filter on
     *
     * @return array
     */
    public function findMany($paths = array(), $limit = false, $offset = false, $ignoreRole = false, $class = null)
    {
        if ($offset) {
            $paths = array_slice($paths, $offset);
        }

        $result = array();
        foreach ($paths as $path) {
            $document = $this->getDocument($path, $ignoreRole, $class);
            if (null === $document) {
                continue;
            }

            $result[] = $document;
            if (false !== $limit) {
                $limit--;
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
     *
     * @param object $document
     *
     * @return boolean
     */
    public function isPublished($document)
    {
        if (null === $this->publishWorkflowChecker) {
            throw new InvalidConfigurationException('You can not check for publication as the publish workflow is not enabled.');
        }

        if (empty($document)) {
            return false;
        }

        return $this->publishWorkflowChecker->isGranted(PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE, $document);
    }

    /**
     * Get the locales of the document
     *
     * @param  string|object $document         Document instance or path
     * @param  Boolean       $includeFallbacks
     * @return array
     */
    public function getLocalesFor($document, $includeFallbacks = false)
    {
        if (is_string($document)) {
            $document = $this->getDm()->find(null, $document);
        }

        if (empty($document)) {
            return array();
        }

        try {
            $locales = $this->getDm()->getLocalesFor($document, $includeFallbacks);
        } catch (MissingTranslationException $e) {
            $locales = array();
        }

        return $locales;
    }

    /**
     * @param string|object $parent parent path/document
     * @param string        $name
     *
     * @return boolean|null|object child or null if the child cannot be found or false if the parent is not in the unit of work
     */
    public function getChild($parent, $name)
    {
        if (is_object($parent)) {
            try {
                $parent = $this->getDm()->getUnitOfWork()->getDocumentId($parent);
            } catch (\Exception $e) {
                return false;
            }
        }

        return $this->getDm()->find(null, "$parent/$name");
    }

    /**
     * Gets child documents.
     *
     * @param string|object  $parent     parent path/document
     * @param int|Boolean    $limit      int limit or false
     * @param string|Boolean $offset     string node name to which to skip to or false
     * @param null|string    $filter     child filter
     * @param Boolean|null   $ignoreRole if the role should be ignored or null if publish workflow should be ignored
     * @param null|string    $class      class name to filter on
     *
     * @return array
     */
    public function getChildren($parent, $limit = false, $offset = false, $filter = null, $ignoreRole = false, $class = null)
    {
        if (empty($parent)) {
            return array();
        }

        if (is_object($parent)) {
            $parent = $this->getDm()->getUnitOfWork()->getDocumentId($parent);
        }
        $node = $this->getDm()->getPhpcrSession()->getNode($parent);
        $children = (array) $node->getNodeNames();
        foreach ($children as $key => $child) {
            // filter before fetching data already to save some traffic
            if (strpos($child, 'phpcr_locale:') === 0) {
                unset($children[$key]);
                continue;
            }
            $children[$key] = "$parent/$child";
        }
        if ($offset) {
            $key = array_search($offset, $children);
            if (false === $key) {
                return array();
            }
            $children = array_slice($children, $key);
        }

        $result = array();
        foreach ($children as $name => $child) {
            // if we requested all children above, we did not filter yet
            if (strpos($name, 'phpcr_locale:') === 0) {
                continue;
            }

            // $child is already a document, but this method also checks access
            $child = $this->getDocument($child, $ignoreRole, $class);
            if (null === $child) {
                continue;
            }

            $result[] = $child;
            if (false !== $limit) {
                $limit--;
                if (!$limit) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Gets linkable child documents.
     *
     * @param string|object  $parent     parent path/document
     * @param int|Boolean    $limit      int limit or false
     * @param string|Boolean $offset     string node name to which to skip to or false
     * @param null|string    $filter     child filter
     * @param Boolean|null   $ignoreRole if the role should be ignored or null if publish workflow should be ignored
     *
     * @return array
     */
    public function getLinkableChildren($parent, $limit = false, $offset = false, $filter = null, $ignoreRole = false)
    {
        return $this->getChildren($parent, $limit, $offset, $filter, $ignoreRole, 'Symfony\Cmf\Component\Routing\RouteReferrersReadInterface');
    }

    /**
     * Gets the paths of children.
     *
     * @param string  $path
     * @param array   $children
     * @param integer $depth
     */
    private function getChildrenPaths($path, array &$children, $depth)
    {
        if (null !== $depth && $depth < 1) {
            return;
        }

        --$depth;

        $node = $this->getDm()->getPhpcrSession()->getNode($path);
        $names = (array) $node->getNodeNames();
        foreach ($names as $name) {
            if (strpos($name, 'phpcr_locale:') === 0) {
                continue;
            }

            $children[] = $child = "$path/$name";
            $this->getChildrenPaths($child, $children, $depth);
        }
    }

    /**
     * @param string|object $parent parent path/document
     * @param null|int      $depth  null denotes no limit, depth of 1 means direct children only etc.
     *
     * @return array
     */
    public function getDescendants($parent, $depth = null)
    {
        if (empty($parent)) {
            return array();
        }

        $children = array();
        if (is_object($parent)) {
            $parent = $this->getDm()->getUnitOfWork()->getDocumentId($parent);
        }
        $this->getChildrenPaths($parent, $children, $depth);

        return $children;
    }

    /**
     * Check children for a possible following document
     *
     * @param array       $childNames
     * @param string      $path
     * @param Boolean     $ignoreRole
     * @param null|string $class
     *
     * @return null|object
     */
    private function checkChildren(array $childNames, $path, $ignoreRole = false, $class = null)
    {
        foreach ($childNames as $name) {
            if (strpos($name, 'phpcr_locale:') === 0) {
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
     * Traverse the depth to find previous documents
     *
     * @param null|integer $depth
     * @param integer      $anchorDepth
     * @param array        $childNames
     * @param string       $path
     * @param Boolean      $ignoreRole
     * @param null|string  $class
     *
     * @return null|object
     */
    private function traversePrevDepth($depth, $anchorDepth, array $childNames, $path, $ignoreRole, $class)
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
     * Search for a previous document
     *
     * @param string|object $path       document instance or path from which to search
     * @param string|object $anchor     document instance or path which serves as an anchor from which to flatten the hierarchy
     * @param null|integer  $depth      depth up to which to traverse down the tree when an anchor is provided
     * @param Boolean       $ignoreRole if to ignore the role
     * @param null|string   $class      the class to filter by
     *
     * @return null|object
     */
    private function searchDepthPrev($path, $anchor, $depth = null, $ignoreRole = false, $class = null)
    {
        if (is_object($path)) {
            $path = $this->getDm()->getUnitOfWork()->getDocumentId($path);
        }

        if (null === $path || '/' === $path) {
            return null;
        }

        $node = $this->getDm()->getPhpcrSession()->getNode($path);

        if (is_object($anchor)) {
            $anchor = $this->getDm()->getUnitOfWork()->getDocumentId($anchor);
        }

        if (0 !== strpos($path, $anchor)) {
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
            $key = array_search($node->getName(), $childNames);
            $childNames = array_slice($childNames, $key + 1);
        }

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

        // check parents
        // TODO do we need to traverse towards the anchor?
        if (0 === strpos($parentPath, $anchor)) {
            $parent = $parent->getParent();
            $childNames = $parent->getNodeNames()->getArrayCopy();
            $key = array_search(PathHelper::getNodeName($parentPath), $childNames);
            $childNames = array_slice($childNames, 0, $key + 1);
            $childNames = array_reverse($childNames);
            $result = $this->checkChildren($childNames, $parent->getPath(), $ignoreRole, $class);
            if ($result) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Search for a next document
     *
     * @param string|object $path       document instance or path from which to search
     * @param string|object $anchor     document instance or path which serves as an anchor from which to flatten the hierarchy
     * @param null|integer  $depth      depth up to which to traverse down the tree when an anchor is provided
     * @param Boolean       $ignoreRole if to ignore the role
     * @param null|string   $class      the class to filter by
     *
     * @return null|object
     */
    private function searchDepthNext($path, $anchor, $depth = null, $ignoreRole = false, $class = null)
    {
        if (is_object($path)) {
            $path = $this->getDm()->getUnitOfWork()->getDocumentId($path);
        }

        if (null === $path || '/' === $path) {
            return null;
        }

        $node = $this->getDm()->getPhpcrSession()->getNode($path);

        if (is_object($anchor)) {
            $anchor = $this->getDm()->getUnitOfWork()->getDocumentId($anchor);
        }

        if (0 !== strpos($path, $anchor)) {
            throw new \RuntimeException("The anchor path '$anchor' is not a parent of the current path '$path'.");
        }

        // take the first eligible child if there are any
        // TODO do we need to traverse away from the anchor up to the depth here?
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
        if (0 === strpos($parentPath, $anchor)) {
            $childNames = $parent->getNodeNames()->getArrayCopy();
            $key = array_search($node->getName(), $childNames);
            $childNames = array_slice($childNames, $key + 1);
            $result = $this->checkChildren($childNames, $parentPath, $ignoreRole, $class);
            if ($result) {
                return $result;
            }
        }

        // take the first eligible parent, traverse up
        while ('/' !== $parentPath) {
            $parent = $parent->getParent();
            if (false === strpos($parent->getPath(), $anchor)) {
                return null;
            }

            $childNames = $parent->getNodeNames()->getArrayCopy();
            $key = array_search(PathHelper::getNodeName($parentPath), $childNames);
            $childNames = array_slice($childNames, $key + 1);
            $parentPath = $parent->getPath();
            $result = $this->checkChildren($childNames, $parentPath, $ignoreRole, $class);
            if ($result) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Search for a following document
     *
     * @param string|object $path       document instance or path from which to search
     * @param Boolean       $reverse    if to traverse back
     * @param Boolean       $ignoreRole if to ignore the role
     * @param null|string   $class      the class to filter by
     *
     * @return null|object
     */
    private function search($path, $reverse = false, $ignoreRole = false, $class = null)
    {
        if (is_object($path)) {
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

        $key = array_search($node->getName(), $childNames);
        $childNames = array_slice($childNames, $key + 1);

        return $this->checkChildren($childNames, $parentNode->getPath(), $ignoreRole, $class);
    }

    /**
     * Gets the previous document.
     *
     * @param string|object      $current    document instance or path from which to search
     * @param null|string|object $anchor     document instance or path which serves as an anchor from which to flatten the hierarchy
     * @param null|integer       $depth      depth up to which to traverse down the tree when an anchor is provided
     * @param Boolean            $ignoreRole if to ignore the role
     * @param null|string        $class      the class to filter by
     *
     * @return null|object
     */
    public function getPrev($current, $anchor = null, $depth = null, $ignoreRole = false, $class = null)
    {
        if ($anchor) {
            return $this->searchDepthPrev($current, $anchor, $depth, true, $ignoreRole, $class);
        }

        return $this->search($current, true, $ignoreRole, $class);
    }

    /**
     * Gets the next document.
     *
     * @param string|object      $current    document instance or path from which to search
     * @param null|string|object $anchor     document instance or path which serves as an anchor from which to flatten the hierarchy
     * @param null|integer       $depth      depth up to which to traverse down the tree when an anchor is provided
     * @param Boolean            $ignoreRole if to ignore the role
     * @param null|string        $class      the class to filter by
     *
     * @return null|object
     */
    public function getNext($current, $anchor = null, $depth = null, $ignoreRole = false, $class = null)
    {
        if ($anchor) {
            return $this->searchDepthNext($current, $anchor, $depth, $ignoreRole, $class);
        }

        return $this->search($current, false, $ignoreRole, $class);
    }

    /**
     * Gets the previous linkable document.
     *
     * @param string|object      $current    document instance or path from which to search
     * @param null|string|object $anchor     document instance or path which serves as an anchor from which to flatten the hierarchy
     * @param null|integer       $depth      depth up to which to traverse down the tree when an anchor is provided
     * @param Boolean            $ignoreRole if to ignore the role
     *
     * @return null|object
     */
    public function getPrevLinkable($current, $anchor = null, $depth = null, $ignoreRole = false)
    {
        return $this->getPrev($current, $anchor, $depth, $ignoreRole, 'Symfony\Cmf\Component\Routing\RouteReferrersReadInterface');
    }

    /**
     * Gets the next linkable document.
     *
     * @param string|object      $current    document instance or path from which to search
     * @param null|string|object $anchor     document instance or path which serves as an anchor from which to flatten the hierarchy
     * @param null|integer       $depth      depth up to which to traverse down the tree when an anchor is provided
     * @param Boolean            $ignoreRole if to ignore the role
     *
     * @return null|object
     */
    public function getNextLinkable($current, $anchor = null, $depth = null, $ignoreRole = false)
    {
        return $this->getNext($current, $anchor, $depth, $ignoreRole, 'Symfony\Cmf\Component\Routing\RouteReferrersReadInterface');
    }
}
