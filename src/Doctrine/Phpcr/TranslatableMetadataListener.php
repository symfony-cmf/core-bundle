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
    public function __construct(
        private string $translationStrategy
    ) {
    }

    /**
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            'loadClassMetadata',
        ];
    }

    /**
     * Handle the load class metadata event: set the translation strategy.
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        /** @var ClassMetadata $meta */
        $meta = $eventArgs->getClassMetadata();

        if ($meta->getReflectionClass()->implementsInterface(TranslatableInterface::class)) {
            $meta->setTranslator($this->translationStrategy);
        }
    }
}
