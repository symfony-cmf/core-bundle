<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
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
     * @param array  $locales   Available locales to select.
     * @param string $formGroup The group name to use for form mapper.
     */
    public function __construct($locales, $formGroup = 'form.group_general')
    {
        $this->locales = $locales;
        $this->formGroup = $formGroup;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with($this->formGroup)
            // do not set a translation_domain for this group or group_general will be translated by our domain.
            ->add('locale', 'choice', array(
                'choices' => array_combine($this->locales, $this->locales),
                'empty_value' => '',
            ), array('translation_domain' => 'CmfCoreBundle'))
            ->end()
        ;
    }

    /**
     * Sanity check and default locale to request locale.
     *
     * {@inheritDoc}
     */
    public function alterNewInstance(AdminInterface $admin, $object)
    {
        if (!$object instanceof TranslatableInterface) {
            throw new \InvalidArgumentException('Expected TranslatableInterface, got ' . get_class($object));
        }

        if ($admin->hasRequest()) {
            $currentLocale = $admin->getRequest()->getLocale();

            if (in_array($currentLocale, $this->locales)) {
                $object->setLocale($currentLocale);
            }
        }
    }
}
