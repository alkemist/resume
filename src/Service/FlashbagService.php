<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatableMessage;

class FlashbagService
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public function send(string $action, $entity = null, $type = 'success'): void
    {
        $parameters = [];

        if ($entity) {
            $className = str_replace('App\\Entity\\', '', get_class($entity));
            $parameters = [
                '%entityType%' => new TranslatableMessage($className),
                '%entityName%' => (string)$entity
            ];
        }

        $this->requestStack->getSession()->getFlashBag()->add(
            $type, new TranslatableMessage('flash_message.' . $action, $parameters, 'messages')
        );
    }
}