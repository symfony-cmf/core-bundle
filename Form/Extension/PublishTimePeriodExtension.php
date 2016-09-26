<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2016 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Form\Extension;

use Symfony\Cmf\Bundle\CoreBundle\Form\Type\PublishTimePeriodType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class PublishTimePeriodExtension extends AbstractTypeExtension
{
    private $extendedType;

    public function __construct($extendedType)
    {
        $this->extendedType = $extendedType;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cmf_core.publish_time_period', PublishTimePeriodType::class, [
            'inherit_data' => true,
        ]);
    }

    public function getExtendedType()
    {
        return $this->extendedType;
    }
}
