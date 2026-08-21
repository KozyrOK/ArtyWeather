<?php

namespace App\Jobs;

use App\DTO\Weather\WeatherCondition;
use App\DTO\Weather\WeatherSnapshot;
use App\Services\AI\AiWeatherPresentationService;
use DateTimeImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateWeatherPresentationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300];

    public function __construct(
        public WeatherSnapshot $snapshot,
        public WeatherCondition $condition,
        public string $locale = 'ru',
    ) {
        $this->onQueue((string) config('queue.connections.redis.queue', 'ai-presentations'));
    }

    public function retryUntil(): DateTimeImmutable
    {
        return new DateTimeImmutable('+15 minutes');
    }

    public function handle(AiWeatherPresentationService $presentations): void
    {
        $presentations->generate($this->snapshot, $this->condition, $this->locale);
    }
}
