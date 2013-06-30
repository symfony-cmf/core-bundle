<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Twig\Extension;

use Symfony\Cmf\Bundle\CoreBundle\Templating\Helper\CmfHelper;

class CmfExtension extends CmfHelper implements \Twig_ExtensionInterface
{
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

    // from \Twig_Extension

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
}
