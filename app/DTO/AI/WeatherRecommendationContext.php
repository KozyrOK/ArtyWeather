<?php

namespace App\DTO\AI;

final readonly class WeatherRecommendationContext
{
    /**
     * @param list<string> $factors
     */
    public function __construct(
        public array $factors,
        public ?string $priority,
    ) {}

    /**
     * @return array{factors: list<string>, priority: ?string}
     */
    public function toArray(): array
    {
        return [
            'factors' => $this->factors,
            'priority' => $this->priority,
        ];
    }
}
