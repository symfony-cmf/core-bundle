<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Admin extension to add publish workflow time period fields.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PublishTimePeriodExtension extends AdminExtension
{
    public function configureFormFields(FormMapper $formMapper)
    {
        $dateOptions = array(
            'empty_value' => '',
            'required' => false,
        );

        $formMapper->with('form.group_publish_workflow', array(
            'translation_domain' => 'CmfCoreBundle'
            ))
            ->add('publish_start_date', 'date', $dateOptions, array(
                'help' => 'form.help_publish_start_date',
            ))
            ->add('publish_end_date', 'date', $dateOptions, array(
                'help' => 'form.help_publish_end_date',
            ))
            ->end();
    }
}
