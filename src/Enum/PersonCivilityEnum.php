<?php

namespace App\Enum;

enum PersonCivilityEnum: string
{
    case Men = 'h';
    case Women = 'f';

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return match ($this) {
            self::Men => 'M',
            self::Women => 'Mme',
        };
    }

    public static function choices(): iterable
    {
        foreach (self::cases() as $case) {
            yield $case->value => $case->toString();
        }
    }
}