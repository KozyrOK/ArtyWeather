<?php

namespace Tests\Unit\Weather;

use App\Services\AI\OllamaWeatherPresentationClient;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class OllamaWeatherPresentationClientTest extends TestCase
{
    public function test_structured_output_json_is_decoded(): void
    {
        Http::fake([
            'ollama:11434/api/chat' => Http::response([
                'message' => [
                    'content' => json_encode([
                        'weather_icon' => 'rain',
                        'landscape' => 'summer_rain',
                        'summary' => 'Rainy summer weather.',
                        'recommendation' => 'Carry an umbrella today.',
                    ]),
                ],
            ]),
        ]);

        $payload = app(OllamaWeatherPresentationClient::class)->generate('system', 'user');

        $this->assertSame('rain', $payload['weather_icon']);
        $this->assertSame('summer_rain', $payload['landscape']);
    }

    public function test_invalid_structured_output_throws_clear_error(): void
    {
        Http::fake([
            'ollama:11434/api/chat' => Http::response(['message' => ['content' => 'not-json']]),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Ollama response content is not valid JSON.');

        app(OllamaWeatherPresentationClient::class)->generate('system', 'user');
    }
}
