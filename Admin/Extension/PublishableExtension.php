<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Admin extension to add publish workflow publishable field.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PublishableExtension extends AdminExtension
{
    /**
     * {@inheritDoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->with('form.group_publish_workflow', array(
            'translation_domain' => 'CmfCoreBundle',
            ))
            ->add('publishable', 'checkbox', array(
                'required' => false,
            ), array(
            ))
            ->end();
    }
}
