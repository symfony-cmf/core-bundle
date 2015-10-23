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
     * @var string
     */
    protected $formTab;

    /**
     * @param string $formGroup - group to use for form mapper
     */
    public function __construct($formGroup = 'form.group_publish_workflow', $formTab = 'form.tab_publish')
    {
        $this->formGroup = $formGroup;
        $this->formTab = $formTab;
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

        if ($formMapper->hasOpenTab()) {
            $formMapper->end();
        }

        $formMapper
            ->tab($this->formTab, array_merge(
                'form.tab_publish' === $this->formTab
                    ? array('translation_domain' => 'CmfCoreBundle')
                    : array()
            ))
                ->with($this->formGroup, 'form.group_publish_workflow' === $this->formGroup
                    ? array('translation_domain' => 'CmfCoreBundle')
                    : array()
                )
                    ->add('publish_start_date', 'date', $dateOptions, array(
                        'help' => 'form.help_publish_start_date',
                    ), array('translation_domain' => 'CmfCoreBundle'))
                    ->add('publish_end_date', 'date', $dateOptions, array(
                        'help' => 'form.help_publish_end_date',
                    ), array('translation_domain' => 'CmfCoreBundle'))
                ->end()
            ->end();
    }
}
