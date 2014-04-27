<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Twig\Extension;

use Symfony\Cmf\Bundle\CoreBundle\Templating\Helper\CmfHelper;

class CmfExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction('cmf_is_linkable', array($this->cmfHelper, 'isLinkable')),
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

        if (interface_exists('Symfony\Cmf\Component\Routing\RouteReferrersReadInterface')) {
            $functions = array_merge($functions, array(
                new \Twig_SimpleFunction('cmf_prev_linkable', array($this->cmfHelper, 'getPrevLinkable')),
                new \Twig_SimpleFunction('cmf_next_linkable', array($this->cmfHelper, 'getNextLinkable')),
                new \Twig_SimpleFunction('cmf_linkable_children', array($this->cmfHelper, 'getLinkableChildren')),
            ));
        }

        return $functions;
    }

    public function getName()
    {
        return 'cmf';
    }
}
