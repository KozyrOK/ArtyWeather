<?php

namespace App\DTO\Weather;

enum WeatherCondition: string
{
    case CLEAR = 'CLEAR';
    case PARTLY_CLOUDY = 'PARTLY_CLOUDY';
    case CLOUDY = 'CLOUDY';
    case RAIN = 'RAIN';
    case HEAVY_RAIN = 'HEAVY_RAIN';
    case SNOW = 'SNOW';
    case FOG = 'FOG';
    case STORM = 'STORM';
}