<?php

namespace App\Http\Requests;

use App\Models\WeatherSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWeatherSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $booleanRules = array_fill_keys(
            WeatherSetting::DISPLAY_FIELDS,
            ['sometimes', 'required', 'boolean']
        );

        return array_merge([
            'latitude' => ['sometimes', 'required', 'numeric', 'min:-90', 'max:90', 'decimal:0,5'],
            'longitude' => ['sometimes', 'required', 'numeric', 'min:-180', 'max:180', 'decimal:0,5'],
            'forecast_period' => ['sometimes', 'required', 'integer', 'min:1', 'max:16'],
            'locale' => ['sometimes', 'required', 'string', 'max:10', Rule::in(['en', 'ru', 'uk'])],
            'theme' => ['sometimes', 'required', 'string', 'max:20', Rule::in(['light', 'dark', 'system'])],
        ], $booleanRules);
    }
}