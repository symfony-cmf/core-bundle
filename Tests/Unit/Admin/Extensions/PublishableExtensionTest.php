<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\Admin\Extension;

use Symfony\Cmf\Bundle\CoreBundle\Admin\Extension\PublishableExtension;

class PublishableExtensionTest extends \PHPUnit_Framework_Testcase
{
    public function setUp()
    {
        $this->formMapper = $this->getMockBuilder(
            'Sonata\AdminBundle\Form\FormMapper'
        )->disableOriginalConstructor()->getMock();

        $this->extension = new PublishableExtension();
    }

    public function testFormMapper()
    {
        $this->formMapper->expects($this->once())
            ->method('with')
            ->will($this->returnSelf());
        $this->formMapper->expects($this->exactly(1))
            ->method('add')
            ->will($this->returnSelf());

        $this->extension->configureFormFields($this->formMapper);
    }
}
