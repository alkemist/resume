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
            self::Men => 'Men',
            self::Women => 'Women',
        };
    }

    public static function choices(): iterable
    {
        foreach (self::cases() as $case) {
            yield $case->value => $case->toString();
        }
    }
}