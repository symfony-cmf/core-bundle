<?php

namespace Symfony\Cmf\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use Symfony\Cmf\Bundle\MenuBundle\Voter\VoterInterface;

/**
 * Set the master request on services tagged with cmf_request_aware
 *
 * The objects must have a method setRequest that accepts a Request as
 * parameter. If they don't, the problem is silently ignored.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class RequestAwareListener implements EventSubscriberInterface
{
    protected $services = array();

    public function onKernelRequest(GetResponseEvent $event)
    {
        foreach ($this->services as $service) {
            $service->setRequest($event->getRequest());
        }
    }

    /**
     * Adds a voter in the matcher.
     *
     * @param object $service which should have a method setRequest
     */
    public function addService($service)
    {
        $this->services[] = $service;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest',
        );
    }
}
