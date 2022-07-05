<?php
namespace App\Enum;

enum DeclarationTypeEnum: string {
    case TVA = 'tva';
    case Social = 'social';
    case Impot = 'impot';
    case CFE = 'cfe';

    public function toString(): string
    {
        return match($this) {
            self::TVA => 'TVA',
            self::Social => 'Social',
            self::Impot => 'Impot',
            self::CFE => 'CFE',
        };
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}