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
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Admin extension to add publish workflow time period fields for models
 * implementing PublishTimePeriodInterface.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PublishTimePeriodExtension extends AdminExtension
{
    /**
     * @var string
     */
    protected $formGroup;

    /**
     * @param string $formGroup - group to use for form mapper
     */
    public function __construct($formGroup = 'form.group_publish_workflow')
    {
        $this->formGroup = $formGroup;
    }

    /**
     * {@inheritDoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $dateOptions = array(
            'empty_value' => '',
            'required' => false,
        );

        $formMapper->with($this->formGroup, array(
            'translation_domain' => 'CmfCoreBundle',
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
