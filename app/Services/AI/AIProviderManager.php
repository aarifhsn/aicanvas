<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\GroqProvider;
use InvalidArgumentException;

class AIProviderManager
{
    protected array $providers = [
        'gemini' => GeminiProvider::class,
        'groq' => GroqProvider::class,
    ];

    public function make(string $name): AIProviderInterface
    {
        if (!isset($this->providers[$name])) {
            throw new InvalidArgumentException("Unknown AI provider: {$name}");
        }

        return app()->make($this->providers[$name]);
    }

    public function available(): array
    {
        return array_keys($this->providers);
    }
}