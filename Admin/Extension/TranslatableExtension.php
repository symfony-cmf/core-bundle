<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Cmf\Bundle\CoreBundle\Model\TranslatableInterface;
use Symfony\Component\HttpFoundation\Request;

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
     * @param array  $locales
     */
    public function __construct($locales)
    {
        $this->locales = $locales;
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('locales', 'choice', array('template' => 'SonataDoctrinePHPCRAdminBundle:CRUD:locales.html.twig'))
        ;
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('form.group_general')
            ->add('locale', 'choice', array(
                'choices' => array_combine($this->locales, $this->locales),
                'empty_value' => '',
            ))
            ->end()
        ;
    }

    public function alterNewInstance(AdminInterface $admin, $object)
    {
        if (!$object instanceof TranslatableInterface) {
            throw new \InvalidArgumentException('Expected TranslatableInterface, got ' . get_class($object));
        }

        if ($admin->hasRequest()) {
            $currentLocale = $admin->getRequest()->attributes->get('_locale');

            if (in_array($currentLocale, $this->locales)) {
                $object->setLocale($currentLocale);
            }
        }

    }
}
