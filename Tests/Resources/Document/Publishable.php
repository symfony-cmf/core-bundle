<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Resources\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodReadInterface;

/**
 * @PHPCR\Document(referenceable=true)
 */
class Publishable implements PublishableReadInterface, PublishTimePeriodReadInterface
{
    /** @PHPCR\Id */
    public $id;

    /** @PHPCR\Field(type="boolean") */
    private $publishable;

    /** @PHPCR\Field(type="date", nullable=true) */
    private $publishStartDate;

    /** @PHPCR\Field(type="date", nullable=true) */
    private $publishEndDate;

    /** @PHPCR\Field(type="date", nullable=true) */
    private $foo;

    public function __construct($publishable = true, $start = null, $end = null)
    {
        $this->publishable = $publishable;
        $this->publishStartDate = $start;
        $this->publishEndDate = $end;
    }

    public function getId()
    {
        return $this->id;
    }

    public function isPublishable()
    {
        return $this->publishable;
    }

    public function getPublishStartDate()
    {
        return $this->publishStartDate;
    }

    public function getPublishEndDate()
    {
        return $this->publishEndDate;
    }
}
