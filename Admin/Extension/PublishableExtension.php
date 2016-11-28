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
use Symfony\Cmf\Bundle\CoreBundle\Form\Type\PublishableType;

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
    protected $formGroup;

    /**
     * @param string $formGroup - group to use for form mapper
     */
    public function __construct($formGroup = 'form.group_publish_workflow')
    {
        $this->formGroup = $formGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with($this->formGroup, ['translation_domain' => 'CmfCoreBundle'])
                ->add('cmf_core_publishable', PublishableType::class, [
                    'inherit_data' => true,
                ])
            ->end()
        ;
    }
}
