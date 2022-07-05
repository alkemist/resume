<?php
namespace App\Enum;

enum OperationTypeEnum: string implements \JsonSerializable
{
    case Income = 'income';
    case Refund = 'refund';
    case Supply = 'supply';
    case Food = 'food';
    case Charge = 'charge';
    case Subscription = 'subscription';
    case Hobby = 'hobby';
    case Other = 'other';
    case Hidden = 'hidden';

    public function toString(): string
    {
        return match($this) {
            self::Income => 'Income',
            self::Refund => 'Refund',
            self::Supply => 'Supply',
            self::Food => 'Food',
            self::Charge => 'Charge',
            self::Subscription => 'Subscription',
            self::Hobby => 'Hobby',
            self::Other => 'Other',
            self::Hidden => 'Hidden',
        };
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}