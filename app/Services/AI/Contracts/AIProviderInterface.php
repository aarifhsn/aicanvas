<?php

namespace App\Services\AI\Contracts;

interface AIProviderInterface
{
    public function generate(string $prompt, array $options = []): string;

    /**
     * @return \Generator<string> yields text chunks as they arrive
     */
    public function stream(string $prompt, array $options = []): \Generator;
}