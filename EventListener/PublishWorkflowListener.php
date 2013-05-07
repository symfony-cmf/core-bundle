<?php

namespace Symfony\Cmf\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowCheckerInterface;

/**
 * Makes sure only published routes and content can be accessed
 */
class PublishWorkflowListener implements EventSubscriberInterface
{
    /**
     * @var PublishWorkflowCheckerInterface
     */
    protected $publishWorkflowChecker;

    /**
     * @param PublishWorkflowCheckerInterface $publishWorkflowChecker
     */
    public function __construct(PublishWorkflowCheckerInterface $publishWorkflowChecker)
    {
        $this->publishWorkflowChecker = $publishWorkflowChecker;
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
        if ($route && !$this->publishWorkflowChecker->checkIsPublished($route, false, $request)) {
            throw new NotFoundHttpException('Route not found at: ' . $request->getPathInfo());
        }

        $content = $request->attributes->get(DynamicRouter::CONTENT_KEY);
        if ($content && !$this->publishWorkflowChecker->checkIsPublished($content, false, $request)) {
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
