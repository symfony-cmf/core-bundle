<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Admin\Extension;

use Symfony\Cmf\Bundle\CoreBundle\Admin\Extension\PublishWorkflowExtension;

class PublishWorkflowExtensionTest extends \PHPUnit_Framework_Testcase
{
    public function setUp()
    {
        $this->formMapper = $this->getMockBuilder(
            'Sonata\AdminBundle\Form\FormMapper'
        )->disableOriginalConstructor()->getMock();

        $this->extension = new PublishWorkflowExtension;
    }

    public function testFormMapper()
    {
        $this->formMapper->expects($this->exactly(3))
            ->method('add');

        $this->extension->configureFormFields($this->formMapper);
    }
}
