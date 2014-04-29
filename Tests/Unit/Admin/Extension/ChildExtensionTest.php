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

use Symfony\Cmf\Bundle\CoreBundle\Admin\Extension\ChildExtension;

class ChildExtensionTest extends \PHPUnit_Framework_Testcase
{
    public function testAlterNewInstance()
    {
        $parent = new \StdClass;

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->any())
            ->method('find')
            ->will($this->returnValue($parent))
        ;

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $request->expects($this->any())
            ->method('get')
            ->will($this->returnValue('parent-id'))
        ;

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager))
        ;
        $admin->expects($this->any())
            ->method('hasRequest')
            ->will($this->returnValue(true))
        ;
        $admin->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request))
        ;

        $child = $this->getMock('Symfony\Cmf\Bundle\CoreBundle\Model\ChildInterface');
        $child->expects($this->once())
            ->method('setParentObject')
            ->with($this->equalTo($parent));

        $extension = new ChildExtension();
        $extension->alterNewInstance($admin, $child);
    }
}
