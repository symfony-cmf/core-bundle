<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form type for rendering a checkbox with a label that can contain links to pages
 *
 * Usage: supply an array with content_ids with the form type options. The form type will generate the
 * urls using the router and replace the array keys from the content_ids array with the urls in the form types label
 *
 * A typical use case is a checkbox the user needs to check to accept terms that are on a different page that has a
 * dynamic route.
 *
 * @author Uwe Jäger <uwej711@googlemail.com>
 */
class CheckboxUrlLabelFormType extends AbstractType
{
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $routes = $options['routes'];
        $paths = array();
        foreach ($routes as $key => $route) {
            $name = isset($route['name']) ? $route['name'] : null;
            $parameters = isset($route['parameters']) ? $route['parameters'] : array();
            $referenceType = isset($route['referenceType']) ? $route['referenceType'] : UrlGeneratorInterface::ABSOLUTE_PATH;
            $paths[$key] = $this->router->generate($name, $parameters, $referenceType);
        }
        $view->vars['paths'] = $paths;
        parent::buildView($view, $form, $options);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'routes' => array(),
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'cmf_core_checkbox_url_label';
    }

    public function getParent()
    {
        return 'checkbox';
    }

}
