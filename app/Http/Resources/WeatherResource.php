<?php

namespace App\Http\Resources;

use App\Services\Weather\WeatherReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WeatherReport */
class WeatherResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'snapshot' => $this->snapshot->toArray(),
            'weather_condition' => $this->condition->value,
            'display' => $this->display,
            'cache' => [
                'status' => $this->cacheStatus,
                'key' => $this->cacheKey,
            ],
        ];
    }
}