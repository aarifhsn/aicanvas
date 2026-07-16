<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->model = config('services.gemini.model');
    }

    public function generate(string $prompt, array $options = []): string
    {
        $response = Http::timeout(30)->post(
            "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}",
            $this->payload($prompt, $options)
        );

        if ($response->failed()) {
            Log::error('Gemini generate failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Gemini request failed with status ' . $response->status());
        }

        return $response->json('candidates.0.content.parts.0.text') ?? '';
    }

    public function stream(string $prompt, array $options = []): \Generator
    {
        $response = Http::withOptions(['stream' => true])
            ->timeout(60)
            ->post(
                "{$this->baseUrl}/models/{$this->model}:streamGenerateContent?alt=sse&key={$this->apiKey}",
                $this->payload($prompt, $options)
            );

        if ($response->failed()) {
            Log::error('Gemini stream failed', ['status' => $response->status()]);
            throw new \RuntimeException('Gemini stream failed with status ' . $response->status());
        }

        $body = $response->toPsrResponse()->getBody();
        $buffer = '';

        while (!$body->eof()) {
            $buffer .= $body->read(1024);

            while (($pos = strpos($buffer, "\n\n")) !== false) {
                $chunk = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 2);

                if (!str_starts_with($chunk, 'data: ')) {
                    continue;
                }

                $json = trim(substr($chunk, 6));
                if ($json === '' || $json === '[DONE]') {
                    continue;
                }

                $decoded = json_decode($json, true);
                $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';

                if ($text !== '') {
                    yield $text;
                }
            }
        }
    }

    private function payload(string $prompt, array $options): array
    {
        return [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_tokens'] ?? 1024,
            ],
        ];
    }
}