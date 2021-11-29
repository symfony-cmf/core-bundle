<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Doctrine\Phpcr;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;

/**
 * Metadata listener for when the translations are globally defined.
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
        return [
            'loadClassMetadata',
        ];
    }

    /**
     * Handle the load class metadata event: set the translation strategy.
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var $meta ClassMetadata */
        $meta = $eventArgs->getClassMetadata();

        if ($meta->getReflectionClass()->implementsInterface(TranslatableInterface::class)) {
            $meta->setTranslator($this->translationStrategy);
        }
    }
}
