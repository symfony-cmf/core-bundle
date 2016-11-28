<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2016 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

class PublishTimePeriodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('publish_start_date', DateType::class, [
                'label' => 'form.label_publish_start_date',
                'translation_domain' => 'CmfCoreBundle',
                'placeholder' => '',
                'required' => false,
            ])
            ->add('publish_end_date', DateType::class, [
                'label' => 'form.label_publish_end_date',
                'translation_domain' => 'CmfCoreBundle',
                'placeholder' => '',
                'required' => false,
            ])
        ;
    }
}
