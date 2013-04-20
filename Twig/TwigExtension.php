<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Twig;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowCheckerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\PHPCR\Exception\MissingTranslationException;
use Doctrine\ODM\PHPCR\DocumentManager;
use PHPCR\Util\PathHelper;

class TwigExtension extends \Twig_Extension
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var PublishWorkflowCheckerInterface
     */
    protected $publishWorkflowChecker;

    /**
     * Instantiate the content controller.
     *
     * @param PublishWorkflowCheckerInterface $publishWorkflowChecker
     * @param ManagerRegistry $registry
     * @param string $objectManagerName
     */
    public function __construct(PublishWorkflowCheckerInterface $publishWorkflowChecker, $registry = null, $objectManagerName = null)
    {
        $this->publishWorkflowChecker = $publishWorkflowChecker;

        if ($registry && $registry instanceof ManagerRegistry) {
            $this->dm = $registry->getManager($objectManagerName);
        }
    }

    /**
     * Get list of available functions
     *
     * @return array
     */
    public function getFunctions()
    {
        $functions = array('cmf_is_published' => new \Twig_Function_Method($this, 'isPublished'));

        if ($this->dm) {
            $functions['cmf_child'] = new \Twig_Function_Method($this, 'getChild');
            $functions['cmf_children'] = new \Twig_Function_Method($this, 'getChildren');
            $functions['cmf_prev'] = new \Twig_Function_Method($this, 'getPrev');
            $functions['cmf_next'] = new \Twig_Function_Method($this, 'getNext');
            $functions['cmf_find'] = new \Twig_Function_Method($this, 'find');
            $functions['cmf_find_many'] = new \Twig_Function_Method($this, 'findMany');
            $functions['cmf_descendants'] = new \Twig_Function_Method($this, 'getDescendants');
            $functions['cmf_nodename'] = new \Twig_Function_Method($this, 'getNodeName');
            $functions['cmf_parent_path'] = new \Twig_Function_Method($this, 'getParentPath');
            $functions['cmf_path'] = new \Twig_Function_Method($this, 'getPath');
            $functions['cmf_document_locales'] = new \Twig_Function_Method($this, 'getLocalesFor');

            if (interface_exists('Symfony\Cmf\Component\Routing\RouteAwareInterface')) {
                $functions['cmf_prev_linkable'] = new \Twig_Function_Method($this, 'getPrevLinkable');
                $functions['cmf_next_linkable'] = new \Twig_Function_Method($this, 'getNextLinkable');
                $functions['cmf_linkable_children'] = new \Twig_Function_Method($this, 'getLinkableChildren');
            }
        }

        return $functions;
    }

    /**
     * Get the extension name
     *
     * @return string
     */
    public function getName()
    {
        return 'cmf_extension';
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
     * Find a document by path
     *
     * @param $path
     * @return null|object
     */
    public function find($path)
    {
        return $this->dm->find(null, $path);
    }

    /**
     * Get a document instance and validate if its eligible
     *
     * @param string|object $document
     * @param Boolean|null $ignoreRole if the role should be ignored or null if publish workflow should be ignored
     * @param null|string $class class name to filter on
     * @return null|object
     */
    private function getDocument($document, $ignoreRole = false, $class = null)
    {
        if (is_string($document)) {
            $document = $this->dm->find(null, $document);
        }

        if (empty($document)
            || (null !== $ignoreRole && !$this->publishWorkflowChecker->checkIsPublished($document, $ignoreRole))
            || (null != $class && !($document instanceof $class))
        ) {
            return null;
        }

        return $document;
    }

    /**
     * @param array $paths list of paths
     * @param int|Boolean $limit int limit or false
     * @param string|Boolean $offset string node name to which to skip to or false
     * @param Boolean|null $ignoreRole if the role should be ignored or null if publish workflow should be ignored
     * @param null|string $class class name to filter on
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
     * Check if a document is published
     *
     * @param $document
     * @return Boolean
     */
    public function isPublished($document)
    {
        if (empty($document)) {
            return false;
        }

        return $this->publishWorkflowChecker->checkIsPublished($document, true);
    }

    /**
     * Get the locales of the document
     *
     * @param string|object $document document instance or path
     * @param Boolean $includeFallbacks
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
     * @param string $name
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
     * Get child documents
     *
     * @param string|object $parent parent path/document
     * @param int|Boolean $limit int limit or false
     * @param string|Boolean $offset string node name to which to skip to or false
     * @param null|string $filter child filter
     * @param Boolean|null $ignoreRole if the role should be ignored or null if publish workflow should be ignored
     * @param null|string $class class name to filter on
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
                if (strpos($child, 'phpcr_locale:') === 0) {
                    unset($children[$key]);
                }
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
            if (strpos($name, 'phpcr_locale:') === 0) {
                continue;
            }

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
     * Get linkable child documents
     *
     * @param string|object $parent parent path/document
     * @param int|Boolean $limit int limit or false
     * @param string|Boolean $offset string node name to which to skip to or false
     * @param null|string $filter child filter
     * @param Boolean|null $ignoreRole if the role should be ignored or null if publish workflow should be ignored
     * @return array
     */
    public function getLinkableChildren($parent, $limit = false, $offset = false, $filter = null, $ignoreRole = false)
    {
        return $this->getChildren($parent, $limit, $offset, $filter, $ignoreRole, 'Symfony\Cmf\Component\Routing\RouteAwareInterface');
    }

    /**
     * Get the paths of children
     *
     * @param string $path
     * @param array $children
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
     * @param null|int $depth null denotes no limit, depth of 1 means direct children only etc.
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
     * @param Boolean $reverse
     * @param string $parentPath
     * @param Boolean $ignoreRole
     * @param null|string $class
     * @param null|string $nodeName
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
     * @param string|object $path document instance or path
     * @param string|object $anchor document instance or path
     * @param null|integer $depth
     * @param Boolean $reverse
     * @param Boolean $ignoreRole
     * @param null|string $class
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
                    $result = $this-> checkChildren($childNames, $reverse, $path, $ignoreRole, $class);
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
     * Get the previous document
     *
     * @param string|object $current document instance or path
     * @param string|object $parent document instance or path
     * @param null|integer $depth
     * @param Boolean $ignoreRole
     * @param null|string $class
     * @return null|object
     */
    public function getPrev($current, $parent = null, $depth = null, $ignoreRole = false, $class = null)
    {
        return $this->search($current, $parent, $depth, true, $ignoreRole, $class);
    }

    /**
     * Get the next document
     *
     * @param string|object $current document instance or path
     * @param string|object $parent document instance or path
     * @param null|integer $depth
     * @param Boolean $ignoreRole
     * @param null|string $class
     * @return null|object
     */
    public function getNext($current, $parent = null, $depth = null, $ignoreRole = false, $class = null)
    {
        return $this->search($current, $parent, $depth, false, $ignoreRole, $class);
    }

    /**
     * Get the previous linkable document
     *
     * @param string|object $current document instance or path
     * @param string|object $parent document instance or path
     * @param null|integer $depth
     * @param Boolean $ignoreRole
     * @return null|object
     */
    public function getPrevLinkable($current, $parent = null, $depth = null, $ignoreRole = false)
    {
        return $this->search($current, $parent, $depth, true, $ignoreRole, 'Symfony\Cmf\Component\Routing\RouteAwareInterface');
    }

    /**
     * Get the next linkable document
     *
     * @param string|object $current document instance or path
     * @param string|object $parent document instance or path
     * @param null|integer $depth
     * @param Boolean $ignoreRole
     * @return null|object
     */
    public function getNextLinkable($current, $parent = null, $depth = null, $ignoreRole = false)
    {
        return $this->search($current, $parent, $depth, false, $ignoreRole, 'Symfony\Cmf\Component\Routing\RouteAwareInterface');
    }
}
