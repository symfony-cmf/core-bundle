<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
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
     * @var array
     */
    protected $locales;

    /**
     * @param array $locales
     */
    public function __construct($locales)
    {
        $this->locales = $locales;
    }

    /**
     * {@inheritDoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('locales', 'choice', array('template' => 'SonataDoctrinePHPCRAdminBundle:CRUD:locales.html.twig'))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('form.group_general')
            ->add('locale', 'choice', array(
                'translation_domain' => 'CmfCoreBundle',
                'choices' => array_combine($this->locales, $this->locales),
                'empty_value' => '',
            ))
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
