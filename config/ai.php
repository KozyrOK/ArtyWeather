<?php

return [
    'default' => env('AI_PROVIDER', 'ollama'),
    'presentation_cache_ttl' => (int) env('AI_PRESENTATION_CACHE_TTL', 3600),

    'providers' => [
        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://ollama:11434'),
            'model' => env('OLLAMA_MODEL', 'llama3.1'),
            'timeout' => (int) env('OLLAMA_TIMEOUT', 30),
            'retries' => (int) env('OLLAMA_RETRIES', 1),
        ],
    ],
];
