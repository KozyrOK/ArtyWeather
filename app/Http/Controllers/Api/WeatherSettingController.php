<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateWeatherSettingsRequest;
use App\Http\Resources\WeatherSettingResource;
use App\Models\User;
use App\Models\WeatherSetting;
use Illuminate\Http\JsonResponse;

class WeatherSettingController extends Controller
{
    public function show(UpdateWeatherSettingsRequest $request): JsonResponse
    {
        $weatherSetting = $this->weatherSettingFor($request->user());

        return WeatherSettingResource::make($weatherSetting->load('user'))->response()->setStatusCode(200);
    }

    public function update(UpdateWeatherSettingsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $weatherSetting = $this->weatherSettingFor($user);

        $userSettings = array_intersect_key($validated, array_flip(['locale', 'theme']));
        if ($userSettings !== []) {
            $user->forceFill($userSettings)->save();
        }

        $weatherSettings = array_diff_key($validated, array_flip(['locale', 'theme']));
        if ($weatherSettings !== []) {
            $weatherSetting->fill($weatherSettings)->save();
        }

        return WeatherSettingResource::make($weatherSetting->refresh()->load('user'))->response()->setStatusCode(200);
    }

    private function weatherSettingFor(User $user): WeatherSetting
    {
        return $user->weatherSetting()->firstOrCreate([], WeatherSetting::defaults());
    }
}