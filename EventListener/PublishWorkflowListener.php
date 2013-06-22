<?php

namespace Symfony\Cmf\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Makes sure only published routes and content can be accessed
 */
class PublishWorkflowListener implements EventSubscriberInterface
{
    /**
     * @var SecurityContext
     */
    protected $context;

    /**
     * @param SecurityContext $context
     */
    public function __construct(SecurityContext $context)
    {
        $this->context = $context;
    }

    /**
     * Handling the request event
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $route = $request->attributes->get(DynamicRouter::ROUTE_KEY);
        if ($route && !$this->context->isGranted('VIEW', $route)) {
            throw new NotFoundHttpException('Route not found at: ' . $request->getPathInfo());
        }

        $content = $request->attributes->get(DynamicRouter::CONTENT_KEY);
        if ($content && !$this->context->isGranted('VIEW', $content)) {
            throw new NotFoundHttpException('Content not found for: ' . $request->getPathInfo());
        }
    }

    /**
     * We are only interested in request events.
     *
     * @return array
     */
    static public function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 1)),
        );
    }

}
