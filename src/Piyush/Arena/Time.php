<?php


declare(strict_types=1);

namespace Piyush\Arena;

class Time {

    public static function calculateTime(int $time): string {
        return gmdate("i:s", $time);
    }
}
