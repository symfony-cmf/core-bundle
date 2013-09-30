<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\Form;

use Symfony\Component\Form\AbstractExtension;
use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;
use Symfony\Cmf\Bundle\CoreBundle\Form\Type\CheckboxUrlLabelFormType;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class Router implements RouterInterface
{
    public function setContext(RequestContext $context) { }
    public function getContext() { }
    public function match($pathinfo) { }
    public function getRouteCollection() { }

    public function generate($name, $parameters = array(), $absolute = false)
    {
        return '/test/'.$name;
    }
}

class CmfCoreExtension extends AbstractExtension
{
    protected function loadTypes()
    {
        return array(
            new CheckboxUrlLabelFormType(new Router())
        );
    }
}

class CheckboxUrlLabelFormTypeTest extends TypeTestCase
{
    public function testContentPathsAreSet()
    {
        $checkboxUrlLabelForm = $this->factory->create('cmf_core_checkbox_url_label', null, array(
            'routes' => array('a' => array('name' => 'a'), 'b' => array('name' => 'b'))
        ));
        $view = $checkboxUrlLabelForm->createView();

        $this->assertSame('/test/a', $view->vars['paths']['a']);
        $this->assertSame('/test/b', $view->vars['paths']['b']);
    }

    protected function getExtensions()
    {
        return array_merge(parent::getExtensions(), array(new CmfCoreExtension()));
    }
}
