<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;

use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;

/**
 * A request listener that makes sure only published routes and content can be
 * accessed.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class PublishWorkflowListener implements EventSubscriberInterface
{
    /**
     * @var PublishWorkflowChecker
     */
    protected $publishWorkflowChecker;

    /**
     * The attribute to check with the workflow checker, typically VIEW or VIEW_ANONYMOUS
     *
     * @var string
     */
    private $publishWorkflowPermission;

    /**
     * @param PublishWorkflowChecker $publishWorkflowChecker
     * @param string                 $attribute              the attribute name to check
     */
    public function __construct(PublishWorkflowChecker $publishWorkflowChecker, $attribute = PublishWorkflowChecker::VIEW_ATTRIBUTE)
    {
        $this->publishWorkflowChecker = $publishWorkflowChecker;
        $this->publishWorkflowPermission = $attribute;
    }

    /**
     * @return string
     */
    public function getPublishWorkflowPermission()
    {
        return $this->publishWorkflowPermission;
    }

    /**
     * @param string $attribute specify what permission to check, typically VIEW or VIEW_ANONYMOUS
     */
    public function setPublishWorkflowPermission($attribute)
    {
        $this->publishWorkflowPermission = $attribute;
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
        if ($route && !$this->publishWorkflowChecker->isGranted($this->getPublishWorkflowPermission(), $route)) {
            throw new NotFoundHttpException('Route not found at: ' . $request->getPathInfo());
        }

        $content = $request->attributes->get(DynamicRouter::CONTENT_KEY);
        if ($content && !$this->publishWorkflowChecker->isGranted($this->getPublishWorkflowPermission(), $content)) {
            throw new NotFoundHttpException('Content not found for: ' . $request->getPathInfo());
        }
    }

    /**
     * We are only interested in request events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 1)),
        );
    }
}
