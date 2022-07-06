<?php

namespace App\Enum;

enum InvoiceStatusEnum: string
{
    case Draft = 'draft';
    case Waiting = 'waiting';
    case Payed = 'payed';

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Waiting => 'Waiting',
            self::Payed => 'Payed',
        };
    }

    public static function choices(): iterable
    {
        foreach (self::cases() as $case) {
            yield $case->value => $case->toString();
        }
    }
}