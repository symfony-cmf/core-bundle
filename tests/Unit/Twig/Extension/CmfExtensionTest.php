<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\Twig\Extension;

use Symfony\Cmf\Bundle\CoreBundle\Templating\Helper\CmfHelper;
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
        $this->cmfHelper = $this->createMock(CmfHelper::class);

        $this->cmfExtension = new CmfExtension($this->cmfHelper);
        $this->env = new \Twig_Environment(new \Twig_Loader_Array([]));
        $this->env->addExtension($this->cmfExtension);
    }

    /**
     * @dataProvider getFunctionsData
     */
    public function testFunctions($methodName, array $methodArguments, $helperMethod = null, array $helperArguments = [])
    {
        if (null === $helperMethod) {
            $helperMethod = $methodName;
            $helperArguments = $methodArguments;
        }

        $helperMethodMock = $this->cmfHelper->expects($this->once())->method($helperMethod);
        if ($helperArguments) {
            call_user_func_array([$helperMethodMock, 'with'], $helperArguments);
        }

        call_user_func_array([$this->cmfExtension, $methodName], $methodArguments);
    }

    public function getFunctionsData()
    {
        return [
            ['isPublished', ['document1']],
            ['isLinkable', ['document1']],
            ['getChild', ['parent', 'name']],
            ['getChildren', ['parent', true], 'getChildren', ['parent', true, false, null, false, null]],
            ['getPrev', ['current'], 'getPrev', ['current', null, null, false, null]],
            ['getNext', ['current'], 'getNext', ['current', null, null, false, null]],
            ['find', ['/cms/simple']],
            ['findTranslation', ['/cms/simple', 'en']],
            ['findMany', [['/cms/simple']], 'findMany', [['/cms/simple'], false, false, false, null]],
            ['getDescendants', ['parent', 2]],
            ['getNodeName', ['document1']],
            ['getParentPath', ['document1']],
            ['getPath', ['document1']],
            ['getLocalesFor', ['document1'], 'getLocalesFor', ['document1', false]],
            ['getPrevLinkable', ['document1'], 'getPrevLinkable', ['document1', null, null, false]],
            ['getNextLinkable', ['document1'], 'getNextLinkable', ['document1', null, null, false]],
            ['getLinkableChildren', ['document1'], 'getLinkableChildren', ['document1', false, false, null, false, null]],
        ];
    }
}
