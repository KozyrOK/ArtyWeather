<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'latitude',
    'longitude',
    'forecast_period',
    'temperature',
    'apparent_temperature',
    'relative_humidity',
    'precipitation',
    'weather_code',
    'cloud_cover',
    'pressure',
    'wind_speed',
    'wind_direction',
    'wind_gusts',
])]
class WeatherSetting extends Model
{
    
    use HasFactory;

    public const DEFAULT_LATITUDE = 55.75583;

    public const DEFAULT_LONGITUDE = 37.61722;

    public const DEFAULT_FORECAST_PERIOD = 7;

    public const DISPLAY_FIELDS = [
        'temperature',
        'apparent_temperature',
        'relative_humidity',
        'precipitation',
        'weather_code',
        'cloud_cover',
        'pressure',
        'wind_speed',
        'wind_direction',
        'wind_gusts',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:5',
            'longitude' => 'decimal:5',
            'forecast_period' => 'integer',
            'temperature' => 'boolean',
            'apparent_temperature' => 'boolean',
            'relative_humidity' => 'boolean',
            'precipitation' => 'boolean',
            'weather_code' => 'boolean',
            'cloud_cover' => 'boolean',
            'pressure' => 'boolean',
            'wind_speed' => 'boolean',
            'wind_direction' => 'boolean',
            'wind_gusts' => 'boolean',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return array_merge([
            'latitude' => self::DEFAULT_LATITUDE,
            'longitude' => self::DEFAULT_LONGITUDE,
            'forecast_period' => self::DEFAULT_FORECAST_PERIOD,
        ], array_fill_keys(self::DISPLAY_FIELDS, true));
    }

    /**
     * Boolean display preferences only control response presentation/UI and must
     * not be used to decide which factual weather variables are requested.
     *
     * @return array<int, string>
     */
    public static function weatherRequestFields(): array
    {
        return self::DISPLAY_FIELDS;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}