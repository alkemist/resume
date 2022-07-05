<?php

namespace App\Enum;

enum InvoicePaymentTypeEnum: string
{
    case Check = 'draft';
    case Transfert = 'Transfert';

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
}