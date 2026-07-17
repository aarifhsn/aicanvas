<?php

namespace Tests\Fakes;

use App\Services\AI\Contracts\AIProviderInterface;

class FakeAIProvider implements AIProviderInterface
{
    public function __construct(protected string $response = 'This is a fake AI response.')
    {
    }

    public function generate(string $prompt, array $options = []): string
    {
        return $this->response;
    }

    public function stream(string $prompt, array $options = []): \Generator
    {
        foreach (explode(' ', $this->response) as $word) {
            yield $word . ' ';
        }
    }
}