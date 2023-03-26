<?php

namespace common\ext\helpers;

use DateTime;
use DateTimeZone;

class DateHelper
{
    public static function convertToDate(?int $time, string $format = 'Y-m-d H:i:s', ?string $timeZone = null)
    {
        $dateTime = new DateTime();
        $dateTime->setTimestamp($time);
        $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));

        return $dateTime->format($format);
    }
}
