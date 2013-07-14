<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\Twig\Extension;

use Symfony\Cmf\Bundle\CoreBundle\Twig\Extension\CmfExtension;

class CmfExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->cmfHelper = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\CoreBundle\Templating\Helper\CmfHelper'
        )->disableOriginalConstructor()->getMock();

        $this->cmfExtension = new CmfExtension($this->cmfHelper);
        $this->env = new \Twig_Environment();
        $this->env->addExtension($this->cmfExtension);
    }


    public function testFunctions()
    {
        $functions = $this->cmfExtension->getFunctions();
        $this->assertCount(15, $functions);
    }
}
