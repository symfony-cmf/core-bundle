<?php

namespace Symfony\Cmf\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Set and update the request on services tagged with cmf_request_aware
 *
 * This listener is emulating the Symfony 2.3 synchronized service behaviour
 * in Symfony 2.2.
 *
 * The objects must have a method setRequest that accepts a Request as
 * parameter. Note that the request may also be null, after the master
 * request is terminated
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class RequestAwareListener implements EventSubscriberInterface
{
    private $services = array();
    private $requestStack = array();

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        array_push($this->requestStack, $request);
        foreach ($this->services as $service) {
            $service->setRequest($request);
        }
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        array_pop($this->requestStack);
        $request = empty($this->requestStack) ? null : end($this->requestStack);
        foreach ($this->services as $service) {
            $service->setRequest($request);
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
            KernelEvents::TERMINATE => 'onKernelTerminate',
        );
    }
}
