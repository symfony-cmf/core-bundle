<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;

/**
 * Admin extension to add publish workflow time period fields.
 *
 * @author David Buchmann <mail@davidbu.ch>
 *
 * @deprecated Since version 1.3, to be removed in 2.0. Use the SonataTranslationBundle instead
 */
class TranslatableExtension extends AdminExtension
{
    /**
     * @var string
     */
    protected $formGroup;

    /**
     * @var array
     */
    protected $locales;

    /**
     * @param array  $locales   Available locales to select
     * @param string $formGroup The group name to use for form mapper
     */
    public function __construct($locales, $formGroup = 'form.group_general')
    {
        @trigger_error('The '.__CLASS__.' class is deprecated since version 1.3 and will be removed in 2.0. Use the SonataTranslationBundle instead.', E_USER_DEPRECATED);

        $this->locales = $locales;
        $this->formGroup = $formGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('locales', 'choice', array(
                'template' => 'SonataDoctrinePHPCRAdminBundle:CRUD:locales.html.twig',
                'translation_domain' => 'CmfCoreBundle',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with($this->formGroup)
            // do not set a translation_domain for this group or group_general will be translated by our domain.
            ->add('locale', method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix') ? 'Symfony\Component\Form\Extension\Core\Type\ChoiceType' : 'choice', array(
                'choices' => array_combine($this->locales, $this->locales),
                'empty_value' => '',
            ), array('translation_domain' => 'CmfCoreBundle'))
            ->end()
        ;
    }

    /**
     * Sanity check and default locale to request locale.
     *
     * {@inheritdoc}
     */
    public function alterNewInstance(AdminInterface $admin, $object)
    {
        if (!$object instanceof TranslatableInterface) {
            throw new \InvalidArgumentException('Expected TranslatableInterface, got '.get_class($object));
        }

        if ($admin->hasRequest()) {
            $currentLocale = $admin->getRequest()->getLocale();

            if (in_array($currentLocale, $this->locales)) {
                $object->setLocale($currentLocale);
            }
        }
    }
}
