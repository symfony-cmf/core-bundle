<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\Admin\Extension;

use Symfony\Cmf\Bundle\CoreBundle\Admin\Extension\PublishTimePeriodExtension;

class PublishTimePeriodExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->formMapper = $this->getMockBuilder(
            'Sonata\AdminBundle\Form\FormMapper'
        )->disableOriginalConstructor()->getMock();

        $this->extension = new PublishTimePeriodExtension('other_group', 'some_tab');
    }

    public function testFormMapper()
    {
        $this->formMapper->expects($this->any())
            ->method('end')
            ->will($this->returnSelf());

        $this->formMapper->expects($this->once())
            ->method('tab')
            ->with('some_tab')
            ->will($this->returnSelf());

        $this->formMapper->expects($this->once())
            ->method('with')
            ->with('other_group')
            ->will($this->returnSelf());

        $this->formMapper->expects($this->exactly(2))
            ->method('add')
            ->will($this->returnSelf());

        $this->extension->configureFormFields($this->formMapper);
    }
}
