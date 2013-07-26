<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Doctrine\Phpcr;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Symfony\Cmf\Bundle\CoreBundle\Model\TranslatableInterface;

/**
 * Metadata listener to remove mapping information that makes fields being
 * translated.
 */
class TranslatableMetadataListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata',
            'postLoad',
        );
    }
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var $meta ClassMetadata */
        $meta = $eventArgs->getClassMetadata();

        if ($meta->getReflectionClass()->implementsInterface('Symfony\Cmf\Bundle\CoreBundle\Model\TranslatableInterface')) {
            foreach($meta->translatableFields as $field) {
                unset($meta->mappings[$field]['translated']);
            }
            $meta->translatableFields = array();
            if (null !== $meta->localeMapping) {
                unset($meta->mappings[$meta->localeMapping]);
                $meta->localeMapping = null;
            }
        }
    }

    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $object = $eventArgs->getObject();
        if ($object instanceof TranslatableInterface) {
            $object->setLocale(false);
        }
    }
}