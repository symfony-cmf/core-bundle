<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Templating\Helper;

use PHPCR\Util\PathHelper;

use Symfony\Component\Templating\Helper\Helper;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\PHPCR\Exception\MissingTranslationException;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
     * @param ManagerRegistry $registry
     * @param string $objectManagerName
     */
    public function __construct(SecurityContextInterface $publishWorkflowChecker = null, $registry = null, $objectManagerName = null)
    {
        $this->publishWorkflowChecker = $publishWorkflowChecker;

        if ($registry && $registry instanceof ManagerRegistry) {
            $this->dm = $registry->getManager($objectManagerName);
        }
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
     * @return boolean|string node name or false if the document is not in the unit of work
     */
    public function getNodeName($document)
    {
        return PathHelper::getNodeName($this->getPath($document));
    }

    /**
     * @param object $document
     * @return boolean|string node name or false if the document is not in the unit of work
     */
    public function getParentPath($document)
    {
        return PathHelper::getParentPath($this->getPath($document));
    }

    /**
     * @param object $document
     * @return boolean|string path or false if the document is not in the unit of work
     */
    public function getPath($document)
    {
        try {
            return $this->dm->getUnitOfWork()->getDocumentId($document);
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
        return $this->dm->find(null, $path);
    }

    /**
     * Gets a document instance and validate if its eligible.
     *
<<<<<<< HEAD:Twig/TwigExtension.php
     * @param string|object $document the id of a document or the document
     *      object itself
     * @param boolean|null $ignoreRole whether the bypass role should be
     *      ignored (leading to only show published content regardless of the
     *      current user) or null to skip the published check completely.
     * @param null|string $class class name to filter on
=======
     * @param string|object $document    the id of a document or the document object itself
     * @param Boolean|null  $ignoreRole  if the role should be ignored or null if publish workflow should be ignored
     * @param null|string   $class class name to filter on
>>>>>>> origin/master:Templating/Helper/CmfHelper.php
     *
     * @return null|object
     */
    private function getDocument($document, $ignoreRole = false, $class = null)
    {
        if (is_string($document)) {
            $document = $this->dm->find(null, $document);
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
     * @param array          $paths       list of paths
     * @param int|Boolean    $limit       int limit or false
     * @param string|Boolean $offset      string node name to which to skip to or false
     * @param Boolean|null   $ignoreRole  if the role should be ignored or null if publish workflow should be ignored
     * @param null|string    $class       class name to filter on
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
<<<<<<< HEAD:Twig/TwigExtension.php
     * Check if a document is published, regardless of the current users role.
     *
     * @param object $document
     *
     * @return boolean
=======
     * Checks if a document is published.
     *
     * @param string $document
     *
     * @return Boolean
>>>>>>> origin/master:Templating/Helper/CmfHelper.php
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
     * @param string|object $document         Document instance or path
     * @param Boolean       $includeFallbacks
     * @return array
     */
    public function getLocalesFor($document, $includeFallbacks = false)
    {
        if (is_string($document)) {
            $document = $this->dm->find(null, $document);
        }

        if (empty($document)) {
            return array();
        }

        try {
            $locales = $this->dm->getLocalesFor($document, $includeFallbacks);
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
                $parent = $this->dm->getUnitOfWork()->getDocumentId($parent);
            } catch (\Exception $e) {
                return false;
            }
        }

        return $this->dm->find(null, "$parent/$name");
    }

    /**
     * Gets child documents.
     *
     * @param string|object  $parent      parent path/document
     * @param int|Boolean    $limit       int limit or false
     * @param string|Boolean $offset      string node name to which to skip to or false
     * @param null|string    $filter      child filter
     * @param Boolean|null   $ignoreRole  if the role should be ignored or null if publish workflow should be ignored
     * @param null|string    $class       class name to filter on
     *
     * @return array
     */
    public function getChildren($parent, $limit = false, $offset = false, $filter = null, $ignoreRole = false, $class = null)
    {
        if (empty($parent)) {
            return array();
        }

        if ($limit || $offset) {
            if (is_object($parent)) {
                $parent = $this->dm->getUnitOfWork()->getDocumentId($parent);
            }
            $node = $this->dm->getPhpcrSession()->getNode($parent);
            $children = (array) $node->getNodeNames();
            foreach ($children as $key => $child) {
                // filter before fetching data already to save some traffic
                if (strpos($child, 'phpcr_locale:') === 0) {
                    unset($children[$key]);
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
        } else {
            $children = $this->dm->getChildren($parent, $filter);
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
     * @param string|object  $parent      parent path/document
     * @param int|Boolean    $limit       int limit or false
     * @param string|Boolean $offset      string node name to which to skip to or false
     * @param null|string    $filter      child filter
     * @param Boolean|null   $ignoreRole  if the role should be ignored or null if publish workflow should be ignored
     *
     * @return array
     */
    public function getLinkableChildren($parent, $limit = false, $offset = false, $filter = null, $ignoreRole = false)
    {
        return $this->getChildren($parent, $limit, $offset, $filter, $ignoreRole, 'Symfony\Cmf\Component\Routing\RouteAwareInterface');
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

        $node = $this->dm->getPhpcrSession()->getNode($path);
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
            $parent = $this->dm->getUnitOfWork()->getDocumentId($parent);
        }
        $this->getChildrenPaths($parent, $children, $depth);

        return $children;
    }

    /**
     * Check children for a possible following document
     *
     * @param \Traversable $childNames
     * @param Boolean      $reverse
     * @param string       $parentPath
     * @param Boolean      $ignoreRole
     * @param null|string  $class
     * @param null|string  $nodeName
     *
     * @return null|object
     */
    private function checkChildren($childNames, $reverse, $parentPath, $ignoreRole = false, $class = null, $nodeName = null)
    {
        if ($reverse) {
            $childNames = array_reverse($childNames->getArrayCopy());
        }

        $check = empty($nodeName);
        foreach ($childNames as $name) {
            if (strpos($name, 'phpcr_locale:') === 0) {
                continue;
            }

            if ($check) {
                try {
                    $child = $this->getDocument("$parentPath/$name", $ignoreRole, $class);
                    if ($child) {
                        return $child;
                    }
                } catch (MissingTranslationException $e) {
                    continue;
                }
            } elseif ($nodeName == $name) {
                $check = true;
            }
        }

        return null;
    }

    /**
     * Search for a following document
     *
     * @param string|object $path       document instance or path
     * @param string|object $anchor     document instance or path
     * @param null|integer  $depth
     * @param Boolean       $reverse
     * @param Boolean       $ignoreRole
     * @param null|string   $class
     *
     * @return null|object
     */
    private function search($path, $anchor = null, $depth = null, $reverse = false, $ignoreRole = false, $class = null)
    {
        if (empty($path)) {
            return null;
        }

        if (is_object($path)) {
            $path = $this->dm->getUnitOfWork()->getDocumentId($path);
        }

        $node = $this->dm->getPhpcrSession()->getNode($path);

        if ($anchor) {
            if (is_object($anchor)) {
                $anchor = $this->dm->getUnitOfWork()->getDocumentId($anchor);
            }

            if (strpos($path, $anchor) !== 0) {
                throw new \RuntimeException("The anchor path '$anchor' is not a parent of the current path '$path'.");
            }

            if (!$reverse
                && (null === $depth || PathHelper::getPathDepth($path() - PathHelper::getPathDepth($anchor)) < $depth)
            ) {
                $childNames = $node->getNodeNames();
                if ($childNames->count()) {
                    $result = $this->checkChildren($childNames, $reverse, $path, $ignoreRole, $class);
                    if ($result) {
                        return $result;
                    }
                }
            }
        }

        $nodename = $node->getName();

        do {
            $parentNode = $node->getParent();
            $childNames = $parentNode->getNodeNames();
            $result = $this->checkChildren($childNames, $reverse, $parentNode->getPath(), $ignoreRole, $class, $nodename);
            if ($result || !$anchor) {
                return $result;
            }

            $node = $parentNode;
            if ($nodename) {
                $reverse = !$reverse;
                $nodename = null;
            }
        } while (!$anchor || $anchor !== $node->getPath());

        return null;
    }

    /**
     * Gets the previous document.
     *
     * @param string|object       $current    document instance or path
     * @param string|object       $parent     document instance or path
     * @param null|integer        $depth
     * @param Boolean             $ignoreRole
     * @param null|string         $class
     *
     * @return null|object
     */
    public function getPrev($current, $parent = null, $depth = null, $ignoreRole = false, $class = null)
    {
        return $this->search($current, $parent, $depth, true, $ignoreRole, $class);
    }

    /**
     * Gets the next document.
     *
     * @param string|object       $current    document instance or path
     * @param string|object       $parent     document instance or path
     * @param null|integer        $depth
     * @param Boolean             $ignoreRole
     * @param null|string         $class
     *
     * @return null|object
     */
    public function getNext($current, $parent = null, $depth = null, $ignoreRole = false, $class = null)
    {
        return $this->search($current, $parent, $depth, false, $ignoreRole, $class);
    }

    /**
     * Gets the previous linkable document.
     *
     * @param string|object       $current    document instance or path
     * @param string|object       $parent     document instance or path
     * @param null|integer        $depth
     * @param Boolean             $ignoreRole
     *
     * @return null|object
     */
    public function getPrevLinkable($current, $parent = null, $depth = null, $ignoreRole = false)
    {
        return $this->search($current, $parent, $depth, true, $ignoreRole, 'Symfony\Cmf\Component\Routing\RouteAwareInterface');
    }

    /**
     * Gets the next linkable document.
     *
     * @param string|object       $current    document instance or path
     * @param string|object       $parent     document instance or path
     * @param null|integer        $depth
     * @param Boolean             $ignoreRole
     *
     * @return null|object
     */
    public function getNextLinkable($current, $parent = null, $depth = null, $ignoreRole = false)
    {
        return $this->search($current, $parent, $depth, false, $ignoreRole, 'Symfony\Cmf\Component\Routing\RouteAwareInterface');
    }
}
