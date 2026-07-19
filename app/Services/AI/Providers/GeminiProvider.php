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

            throw new \RuntimeException('Gemini request failed with status ' . $response->status());
        }

        $data = $response->json();

        // Check for API error in response body
        if (isset($data['error'])) {
            $errorMessage = $data['error']['message'] ?? 'Unknown Gemini API error';
            throw new \RuntimeException('Gemini API error: ' . $errorMessage);
        }

        return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
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
            throw new \RuntimeException('Gemini stream failed with status ' . $response->status());
        }

        $body = $response->toPsrResponse()->getBody();
        $buffer = '';

        while (!$body->eof()) {
            $buffer .= $body->read(1024);

            // Handle both \r\n\r\n (standard SSE) and \n\n line endings
            while (($pos = strpos($buffer, "\r\n\r\n")) !== false) {
                $chunk = trim(substr($buffer, 0, $pos));
                $buffer = substr($buffer, $pos + 4);

                if (!str_starts_with($chunk, 'data: ')) {
                    continue;
                }

                $json = trim(substr($chunk, 6));
                if ($json === '' || $json === '[DONE]') {
                    continue;
                }

                $decoded = json_decode($json, true);

                // Check for API error in stream response
                if (isset($decoded['error'])) {
                    $errorMessage = $decoded['error']['message'] ?? 'Unknown Gemini API error';
                    throw new \RuntimeException('Gemini API error: ' . $errorMessage);
                }

                $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';

                if ($text !== '') {
                    yield $text;
                }
            }
        }

        // Process any remaining data in buffer
        $buffer = trim($buffer);
        if ($buffer && str_starts_with($buffer, 'data: ')) {
            $json = trim(substr($buffer, 6));
            if ($json !== '' && $json !== '[DONE]') {
                $decoded = json_decode($json, true);
                if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
                    $text = $decoded['candidates'][0]['content']['parts'][0]['text'];
                    if ($text !== '') {
                        yield $text;
                    }
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