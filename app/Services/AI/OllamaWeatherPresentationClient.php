<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OllamaWeatherPresentationClient
{
    /** @return array<string, mixed> */
    public function generate(string $systemPrompt, string $userPrompt): array
    {
        $response = Http::timeout((int) config('ai.providers.ollama.timeout', 30))
            ->retry((int) config('ai.providers.ollama.retries', 1), 200)
            ->post(rtrim((string) config('ai.providers.ollama.base_url'), '/').'/api/chat', [
                'model' => config('ai.providers.ollama.model'),
                'stream' => false,
                'format' => 'json',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Ollama presentation request failed.');
        }

        $content = data_get($response->json(), 'message.content');
        if (! is_string($content)) {
            throw new RuntimeException('Ollama response does not contain message content.');
        }

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('Ollama response content is not valid JSON.');
        }

        return $decoded;
    }
}
