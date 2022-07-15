<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatableMessage;

class FlashbagService
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public function send(string $action, $entity, $type = 'success'): void
    {
        $className = str_replace('App\\Entity\\', '', get_class($entity));

        $this->requestStack->getSession()->getFlashBag()->add(
            $type, new TranslatableMessage('flash_message.' . $action, [
            '%entityType%' => new TranslatableMessage($className),
            '%entityName%' => (string)$entity,
        ],                                 'messages')
        );
    }
}