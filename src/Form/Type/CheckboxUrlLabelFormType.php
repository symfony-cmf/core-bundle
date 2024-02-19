<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Form type for rendering a checkbox with a label that can contain links to
 * pages.
 *
 * Usage: supply an array with routes information with the form type options.
 * The form type will generate the urls using the router and replace the array
 * keys from the routes array with the urls in the form types label.
 *
 * A typical use case is a checkbox the user needs to check to accept terms
 * that are on a different page that has a dynamic route.
 *
 * @author Uwe Jäger <uwej711@googlemail.com>
 */
class CheckboxUrlLabelFormType extends AbstractType
{
    public function __construct(
        private RouterInterface $router
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $routes = $options['routes'];
        $paths = [];
        foreach ($routes as $key => $route) {
            $name = $route['name'] ?? '';
            $parameters = $route['parameters'] ?? [];
            $referenceType = $route['referenceType'] ?? UrlGeneratorInterface::ABSOLUTE_PATH;
            $paths[$key] = $this->router->generate($name, $parameters, $referenceType);
        }
        $view->vars['paths'] = $paths;
        parent::buildView($view, $form, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'routes' => [],
        ]);
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'cmf_core_checkbox_url_label';
    }

    public function getParent(): ?string
    {
        return CheckboxType::class;
    }
}
