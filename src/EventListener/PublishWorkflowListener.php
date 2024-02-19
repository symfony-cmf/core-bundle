<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\EventListener;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * A request listener that makes sure only published routes and content can be
 * accessed.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class PublishWorkflowListener implements EventSubscriberInterface
{
    public function __construct(
        private PublishWorkflowChecker $publishWorkflowChecker,
        /**
         * The attribute to check with the workflow checker, typically VIEW or VIEW_ANONYMOUS.
         */
        private string $publishWorkflowPermission = PublishWorkflowChecker::VIEW_ATTRIBUTE)
    {
    }

    public function getPublishWorkflowPermission(): string
    {
        return $this->publishWorkflowPermission;
    }

    public function setPublishWorkflowPermission(string $attribute): void
    {
        $this->publishWorkflowPermission = $attribute;
    }

    /**
     * Handling the request event.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $route = $request->attributes->get(DynamicRouter::ROUTE_KEY);
        if ($route && !$this->publishWorkflowChecker->isGranted($this->getPublishWorkflowPermission(), $route)) {
            throw new NotFoundHttpException('Route not found at: '.$request->getPathInfo());
        }

        $content = $request->attributes->get(DynamicRouter::CONTENT_KEY);
        if ($content && !$this->publishWorkflowChecker->isGranted($this->getPublishWorkflowPermission(), $content)) {
            throw new NotFoundHttpException('Content not found for: '.$request->getPathInfo());
        }
    }

    /**
     * We are only interested in request events.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 1]],
        ];
    }
}
