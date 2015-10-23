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
 * Admin extension to add a publish workflow publishable field for models
 * implementing PublishableInterface.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PublishableExtension extends AdminExtension
{
    /**
     * @var string
     */
    protected $formTab;

    /**
     * @var string
     */
    protected $formGroup;

    /**
     * @param string $formTab The tab to put the new publishable field
     */
    public function __construct($formGroup = 'form.group_publish_workflow', $formTab = 'form.tab_publish')
    {
        $this->formTab = $formTab;
        $this->formGroup = $formGroup;
    }

    /**
     * {@inheritDoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
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
                    ->add('publishable', 'checkbox', array(
                        'required' => false,
                    ), array(
                        'translation_domain' => 'CmfCoreBundle',
                        'help' => 'form.help_publishable'
                    ))
                ->end()
            ->end();
    }
}
