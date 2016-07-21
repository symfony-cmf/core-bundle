<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\Twig\Extension;

use Symfony\Cmf\Bundle\CoreBundle\Twig\Extension\CmfExtension;

class CmfExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $cmfHelper;
    /**
     * @var \Twig_Environment
     */
    private $env;

    /**
     * @var CmfExtension
     */
    private $cmfExtension;

    public function setUp()
    {
        $this->cmfHelper = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\CoreBundle\Templating\Helper\CmfHelper'
        )->disableOriginalConstructor()->getMock();

        $this->cmfExtension = new CmfExtension($this->cmfHelper);
        $this->env = new \Twig_Environment(new \Twig_Loader_Array(array()));
        $this->env->addExtension($this->cmfExtension);
    }

    /**
     * @dataProvider getFunctionsData
     */
    public function testFunctions($methodName, array $methodArguments, $helperMethod = null, array $helperArguments = array())
    {
        if (null === $helperMethod) {
            $helperMethod = $methodName;
            $helperArguments = $methodArguments;
        }

        $helperMethodMock = $this->cmfHelper->expects($this->once())->method($helperMethod);
        if ($helperArguments) {
            call_user_func_array(array($helperMethodMock, 'with'), $helperArguments);
        }

        call_user_func_array(array($this->cmfExtension, $methodName), $methodArguments);
    }

    public function getFunctionsData()
    {
        return array(
            array('isPublished', array('document1')),
            array('isLinkable', array('document1')),
            array('getChild', array('parent', 'name')),
            array('getChildren', array('parent', true), 'getChildren', array('parent', true, false, null, false, null)),
            array('getPrev', array('current'), 'getPrev', array('current', null, null, false, null)),
            array('getNext', array('current'), 'getNext', array('current', null, null, false, null)),
            array('find', array('/cms/simple')),
            array('findTranslation', array('/cms/simple', 'en')),
            array('findMany', array(array('/cms/simple')), 'findMany', array(array('/cms/simple'), false, false, false, null)),
            array('getDescendants', array('parent', 2)),
            array('getNodeName', array('document1')),
            array('getParentPath', array('document1')),
            array('getPath', array('document1')),
            array('getLocalesFor', array('document1'), 'getLocalesFor', array('document1', false)),
            array('getPrevLinkable', array('document1'), 'getPrevLinkable', array('document1', null, null, false)),
            array('getNextLinkable', array('document1'), 'getNextLinkable', array('document1', null, null, false)),
            array('getLinkableChildren', array('document1'), 'getLinkableChildren', array('document1', false, false, null, false, null)),
        );
    }
}
