<?php

namespace App\Enum;

use Traversable;

enum InvoiceStatusEnum: string
{
    case Draft = 'draft';
    case Waiting = 'waiting';
    case Sent = 'sent';
    case Payed = 'payed';

    public static function choices(): Traversable
    {
        foreach (self::cases() as $case) {
            yield $case->value => $case->toString();
        }
    }

    public function toString(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Waiting => 'Waiting',
            self::Sent => 'Sent',
            self::Payed => 'Payed',
        };
    }

    public static function values(): Traversable
    {
        foreach (self::cases() as $case) {
            yield $case->toString() => $case->value;
        }
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}