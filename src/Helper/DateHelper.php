<?php

namespace App\Helper;

use DateTime;

class DateHelper
{
    public static function lastDayOfMonth(\DateTime $date): \DateTime
    {
        return $date->modify('last day of');
    }

    public static function frToEn(string $dateString): string|false {
        return DateTime::createFromFormat('d/m/Y', $dateString)->format('m/d/Y');
    }
}