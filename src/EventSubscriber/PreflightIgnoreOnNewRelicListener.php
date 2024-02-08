<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PreflightIgnoreOnNewRelicListener
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(FinishRequestEvent $event): void
    {
        if ('OPTIONS' === $event->getRequest()->getMethod()) {
            die();
        }
    }
}