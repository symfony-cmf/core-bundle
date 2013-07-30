<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Doctrine\Phpcr;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;

/**
 * Metadata listener for when translations are disabled in PHPCR-ODM to remove
 * mapping information that makes fields being translated.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class TranslatableMetadataListener implements EventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata',
            'postLoad',
        );
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

        if ($meta->getReflectionClass()->implementsInterface('Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface')) {
            foreach ($meta->translatableFields as $field) {
                unset($meta->mappings[$field]['translated']);
            }
            $meta->translatableFields = array();
            if (null !== $meta->localeMapping) {
                unset($meta->mappings[$meta->localeMapping]);
                $meta->localeMapping = null;
            }
        }
    }

    /**
     * We set the locale field to false so that other code can use the
     * information that translations are deactivated.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $object = $eventArgs->getObject();
        if ($object instanceof TranslatableInterface) {
            $object->setLocale(false);
        }
    }
}
