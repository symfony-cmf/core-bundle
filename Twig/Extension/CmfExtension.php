<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Twig\Extension;

use Symfony\Cmf\Bundle\CoreBundle\Templating\Helper\CmfHelper;

class CmfExtension implements \Twig_ExtensionInterface
{
    protected $cmfHelper;

    public function __construct(CmfHelper $cmfHelper)
    {
        $this->cmfHelper = $cmfHelper;
    }

    /**
     * Get list of available functions
     *
     * @return array
     */
    public function getFunctions()
    {
        $functions = array(
            new \Twig_SimpleFunction('cmf_is_published', array($this->cmfHelper, 'isPublished')),
            new \Twig_SimpleFunction('cmf_child', array($this->cmfHelper, 'getChild')),
            new \Twig_SimpleFunction('cmf_children', array($this->cmfHelper, 'getChildren')),
            new \Twig_SimpleFunction('cmf_prev', array($this->cmfHelper, 'getPrev')),
            new \Twig_SimpleFunction('cmf_next', array($this->cmfHelper, 'getNext')),
            new \Twig_SimpleFunction('cmf_find', array($this->cmfHelper, 'find')),
            new \Twig_SimpleFunction('cmf_find_many', array($this->cmfHelper, 'findMany')),
            new \Twig_SimpleFunction('cmf_descendants', array($this->cmfHelper, 'getDescendants')),
            new \Twig_SimpleFunction('cmf_nodename', array($this->cmfHelper, 'getNodeName')),
            new \Twig_SimpleFunction('cmf_parent_path', array($this->cmfHelper, 'getParentPath')),
            new \Twig_SimpleFunction('cmf_path', array($this->cmfHelper, 'getPath')),
            new \Twig_SimpleFunction('cmf_document_locales', array($this->cmfHelper, 'getLocalesFor')),
        );

        if (interface_exists('Symfony\Cmf\Component\Routing\RouteAwareInterface')) {
            $functions = array_merge($functions, array(
                new \Twig_SimpleFunction('cmf_prev_linkable', array($this->cmfHelper, 'getPrevLinkable')),
                new \Twig_SimpleFunction('cmf_next_linkable', array($this->cmfHelper, 'getNextLinkable')),
                new \Twig_SimpleFunction('cmf_linkable_children', array($this->cmfHelper, 'getLinkableChildren')),
            ));
        }

        return $functions;
    }

    /**
     * Initializes the runtime environment.
     *
     * This is where you can load some file that contains filter functions for instance.
     *
     * @param Twig_Environment $environment The current Twig_Environment instance
     */
    public function initRuntime(\Twig_Environment $environment)
    {
    }

    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
     */
    public function getTokenParsers()
    {
        return array();
    }

    /**
     * Returns the node visitor instances to add to the existing list.
     *
     * @return array An array of Twig_NodeVisitorInterface instances
     */
    public function getNodeVisitors()
    {
        return array();
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array();
    }

    /**
     * Returns a list of tests to add to the existing list.
     *
     * @return array An array of tests
     */
    public function getTests()
    {
        return array();
    }

    /**
     * Returns a list of operators to add to the existing list.
     *
     * @return array An array of operators
     */
    public function getOperators()
    {
        return array();
    }

    /**
     * Returns a list of global variables to add to the existing list.
     *
     * @return array An array of global variables
     */
    public function getGlobals()
    {
        return array();
    }

    public function getName()
    {
        return 'cmf';
    }
}
