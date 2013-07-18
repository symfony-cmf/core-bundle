<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\Form;

use Symfony\Component\Form\AbstractExtension;
use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;
use Symfony\Cmf\Bundle\CoreBundle\Form\Type\CheckboxUrlLabelFormType;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
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
        return '/test'.$parameters['content_id'];
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
            'content_ids' => array('a' => '/content/a', 'b' => '/content/b')
        ));
        $view = $checkboxUrlLabelForm->createView();

        $this->assertSame('/test/content/a', $view->vars['content_paths']['a']);
        $this->assertSame('/test/content/b', $view->vars['content_paths']['b']);
    }

    protected function getExtensions()
    {
        return array_merge(parent::getExtensions(), array(new CmfCoreExtension()));
    }
}