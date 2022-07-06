<?php

namespace App\Enum;

enum InvoicePaymentTypeEnum: string
{
    case Check = 'check';
    case Transfert = 'transfert';

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return match ($this) {
            self::Check => 'Check',
            self::Transfert => 'Transfert',
        };
    }

    public static function choices(): iterable
    {
        foreach (self::cases() as $case) {
            yield $case->value => $case->toString();
        }
    }
}