<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Doctrine\Phpcr;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;

/**
 * Metadata listener for when the translations is globally defined
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class TranslatableMetadataListener implements EventSubscriber
{
    /**
     * @var string
     */
    private $translationStrategy;

    /**
     * @param string $translationStrategy
     */
    public function __construct($translationStrategy)
    {
        $this->translationStrategy = $translationStrategy;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata',
        );
    }

    /**
     * Handle the load class metadata event: set the translation strategy
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var $meta ClassMetadata */
        $meta = $eventArgs->getClassMetadata();

        if ($meta->getReflectionClass()->implementsInterface('Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface')) {
            $meta->setTranslator($this->translationStrategy);
        }
    }
}
