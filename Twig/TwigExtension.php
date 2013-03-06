<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Twig;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowCheckerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\PHPCR\Exception\MissingTranslationException;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Component\Routing\RouteAwareInterface;

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
     * @param ManagerRegistry $registry
     * @param string $objectManagerName
     * @param PublishWorkflowCheckerInterface $publishWorkflowChecker
     */
    public function __construct(ManagerRegistry $registry, $objectManagerName, PublishWorkflowCheckerInterface $publishWorkflowChecker)
    {
        $this->dm = $registry->getManager($objectManagerName);
        $this->publishWorkflowChecker = $publishWorkflowChecker;
    }

    public function getFunctions()
    {
        return array(
            'cmf_child' => new \Twig_Function_Method($this, 'child'),
            'cmf_children' => new \Twig_Function_Method($this, 'children'),
            'cmf_prev' => new \Twig_Function_Method($this, 'prev'),
            'cmf_next' => new \Twig_Function_Method($this, 'next'),
            'cmf_is_published' => new \Twig_Function_Method($this, 'isPublished'),
            'cmf_find' => new \Twig_Function_Method($this, 'find'),
            'cmf_document_locales' => new \Twig_Function_Method($this, 'getLocalesFor'),
        );
    }

    public function child($parent, $name)
    {
        $parentId = $this->dm->getUnitOfWork()->getDocumentId($parent);
        return $this->dm->find(null, $parentId.'/'.$name);
    }

    public function children($parent, $limit = false, $ignoreRole = false, $filter = null)
    {
        if (empty($parent)) {
            return array();
        }

        $children = $this->dm->getChildren($parent, $filter);

        $result = array();
        foreach ($children as $child) {
            if (!$this->publishWorkflowChecker->checkIsPublished($child, $ignoreRole)) {
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

    private function search($current, $reverse = false)
    {
        if (empty($current)) {
            return null;
        }

        $path = $this->dm->getUnitOfWork()->getDocumentId($current);
        $node = $this->dm->getPhpcrSession()->getNode($path);
        $parent = $node->getParent();
        $childNames = $parent->getNodeNames();
        if ($reverse) {
            $childNames = array_reverse($childNames->getArrayCopy());
        }

        $check = false;
        foreach ($childNames as $name) {
            if ($check) {
                try {
                    $child = $this->dm->find(null, $parent->getPath().'/'.$name);
                    if ($this->publishWorkflowChecker->checkIsPublished($child) && $child instanceof RouteAwareInterface) {
                        return $child;
                    }
                } catch (MissingTranslationException $e) {
                    continue;
                }
            }

            if ($node->getName() == $name) {
                $check = true;
            }
        }

        return null;
    }

    public function prev($current)
    {
        return $this->search($current, true);
    }

    public function next($current)
    {
        return $this->search($current);
    }

    public function isPublished($document)
    {
        if (empty($document)) {
            return false;
        }

        return $this->publishWorkflowChecker->checkIsPublished($document, true);
    }

    public function find($path)
    {
        return $this->dm->find(null, $path);
    }

    public function getLocalesFor($document, $includeFallbacks = false)
    {
        try {
            if (empty($document)) {
                return array();
            }

            $locales = $this->dm->getLocalesFor($document, $includeFallbacks);
        } catch (MissingTranslationException $e) {
            $locales = array();
        }

        return $locales;
    }

    public function getName()
    {
        return 'children_extension';
    }
}