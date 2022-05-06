<?php


declare(strict_types=1);

namespace Piyush\Arena;

class Vector3 extends \pocketmine\math\Vector3 {

    public function __toString() {
        return "$this->x,$this->y,$this->z";
    }

    public static function fromString(string $string) {
        return new Vector3((int)explode(",", $string)[0], (int)explode(",", $string)[1], (int)explode(",", $string)[2]);
    }
}