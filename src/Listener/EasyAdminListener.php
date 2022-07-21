<?php

declare(strict_types=1);

namespace App\Listener;

use App\Service\FlashbagService;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EasyAdminListener implements EventSubscriberInterface
{
    public function __construct(private readonly FlashbagService $flashbagService)
    {
    }

    #[ArrayShape([AfterEntityPersistedEvent::class => "string[]", AfterEntityUpdatedEvent::class => "string[]", AfterEntityDeletedEvent::class => "string[]"])]
    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityPersistedEvent::class => ['flashMessageAfterPersist'],
            AfterEntityUpdatedEvent::class   => ['flashMessageAfterUpdate'],
            AfterEntityDeletedEvent::class   => ['flashMessageAfterDelete'],
        ];
    }

    public function flashMessageAfterPersist(AfterEntityPersistedEvent $event): void
    {
        $this->flashbagService->send('create', $event->getEntityInstance());
    }

    public function flashMessageAfterUpdate(AfterEntityUpdatedEvent $event): void
    {
        $this->flashbagService->send('update', $event->getEntityInstance());
    }

    public function flashMessageAfterDelete(AfterEntityDeletedEvent $event): void
    {
        $this->flashbagService->send('delete', $event->getEntityInstance());
    }
}