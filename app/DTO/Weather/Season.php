<?php

namespace App\DTO\Weather;

enum Season: string
{
    case SPRING = 'SPRING';
    case SUMMER = 'SUMMER';
    case AUTUMN = 'AUTUMN';
    case WINTER = 'WINTER';

    public static function fromMonth(int $month): self
    {
        return match ($month) {
            3, 4, 5 => self::SPRING,
            6, 7, 8 => self::SUMMER,
            9, 10, 11 => self::AUTUMN,
            default => self::WINTER,
        };
    }

    public function assetPrefix(): string
    {
        return strtolower($this->value);
    }
}
