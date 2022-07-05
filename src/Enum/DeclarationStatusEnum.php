<?php

namespace App\Enum;

enum DeclarationStatusEnum: string
{
    case Waiting = 'waiting';
    case Payed = 'payed';

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return match ($this) {
            self::Waiting => 'Waiting',
            self::Payed => 'Payed',
        };
    }
}