<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Admin extension to add publish workflow fields.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PublishWorkflowExtension extends AdminExtension
{
    public function configureFormFields(FormMapper $formMapper)
    {
        $dateOptions = array(
            'empty_value' => '',
            'required' => false,
        );

        $formMapper->with('form.group_publish_workflow')
            ->add('publishable', 'checkbox', array(
                'required' => false,
            ))
            ->add('publish_start_date', 'date', $dateOptions)
            ->add('publish_end_date', 'date', $dateOptions)
            ->end();
    }
}
