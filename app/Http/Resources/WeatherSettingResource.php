<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WeatherSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'forecast_period' => $this->forecast_period,
            'display' => [
                'temperature' => $this->temperature,
                'apparent_temperature' => $this->apparent_temperature,
                'relative_humidity' => $this->relative_humidity,
                'precipitation' => $this->precipitation,
                'weather_code' => $this->weather_code,
                'cloud_cover' => $this->cloud_cover,
                'pressure' => $this->pressure,
                'wind_speed' => $this->wind_speed,
                'wind_direction' => $this->wind_direction,
                'wind_gusts' => $this->wind_gusts,
            ],
            'weather_request_fields' => $this->resource::weatherRequestFields(),
            'user' => [
                'locale' => $this->whenLoaded('user', fn () => $this->user->locale),
                'theme' => $this->whenLoaded('user', fn () => $this->user->theme),
            ],
        ];
    }
}