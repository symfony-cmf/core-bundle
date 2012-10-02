<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Twig;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowCheckerInterface;
use Doctrine\ODM\PHPCR\DocumentManager;

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
     */
    public function __construct(DocumentManager $dm, PublishWorkflowCheckerInterface $publishWorkflowChecker)
    {
        $this->dm = $dm;
        $this->publishWorkflowChecker = $publishWorkflowChecker;
    }

    public function getFunctions()
    {
        return array(
            'cmf_children' => new \Twig_Function_Method($this, 'children'),
            'cmf_prev' => new \Twig_Function_Method($this, 'prev'),
            'cmf_next' => new \Twig_Function_Method($this, 'next'),
            'cmf_is_published' => new \Twig_Function_Method($this, 'isPublished'),
        );
    }

    public function children($current, $limit = false)
    {
        $children = $this->dm->getChildren($current);
        foreach ($children as $key => $child) {
            if (!$this->publishWorkflowChecker->checkIsPublished($child)) {
                $children->remove($key);
            }

            if (false !== $limit) {
                $limit--;
                if (!$limit) {
                    break;
                }
            }
        }

        return $children;
    }

    private function search($current, $reverse = false)
    {
        // TODO optimize
        $path = $this->dm->getUnitOfWork()->getDocumentId($current);
        $node = $this->dm->getPhpcrSession()->getNode($path);
        $parent = $node->getParent();
        $children = $parent->getNodes();

        $childNames = array_keys($children->getArrayCopy());
        if ($reverse) {
            $childNames = array_reverse($childNames);
        }

        $check = false;
        foreach ($childNames as $name) {
            if ($check) {
                $child = $this->dm->find(null, $parent->getPath().'/'.$name);
                if ($this->publishWorkflowChecker->checkIsPublished($child)) {
                    return $child;
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
        return $this->publishWorkflowChecker->checkIsPublished($document, true);
    }

    public function getName()
    {
        return 'children_extension';
    }
}