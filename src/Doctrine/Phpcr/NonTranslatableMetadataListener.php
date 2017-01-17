<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Doctrine\Phpcr;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;

/**
 * Metadata listener for when translations are disabled in PHPCR-ODM to remove
 * mapping information that makes fields being translated.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class NonTranslatableMetadataListener implements EventSubscriber
{
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
     * Handle the load class metadata event: remove translated attribute from
     * fields and remove the locale mapping if present.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var $meta ClassMetadata */
        $meta = $eventArgs->getClassMetadata();

        if (!$meta->translator) {
            return;
        }

        foreach ($meta->translatableFields as $field) {
            unset($meta->mappings[$field]['translated']);
        }
        $meta->translatableFields = [];
        if (null !== $meta->localeMapping) {
            unset($meta->mappings[$meta->localeMapping]);
            $meta->localeMapping = null;
        }
        $meta->translator = null;
    }
}
