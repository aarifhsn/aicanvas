<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://api.groq.com/openai/v1';

    public function __construct()
    {
        $this->apiKey = config('services.groq.key');
        $this->model = config('services.groq.model');
    }

    public function generate(string $prompt, array $options = []): string
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post("{$this->baseUrl}/chat/completions", $this->payload($prompt, $options));

        if ($response->failed()) {
            throw new \RuntimeException('Groq request failed with status ' . $response->status());
        }

        $data = $response->json();

        // Check for API error in response body
        if (isset($data['error'])) {
            $errorMessage = $data['error']['message'] ?? 'Unknown Groq API error';
            throw new \RuntimeException('Groq API error: ' . $errorMessage);
        }

        return $data['choices'][0]['message']['content'] ?? '';
    }

    public function stream(string $prompt, array $options = []): \Generator
    {
        $response = Http::withToken($this->apiKey)
            ->withOptions(['stream' => true])
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", $this->payload($prompt, $options, stream: true));

        if ($response->failed()) {
            throw new \RuntimeException('Groq stream failed with status ' . $response->status());
        }

        $body = $response->toPsrResponse()->getBody();
        $buffer = '';

        while (!$body->eof()) {
            $buffer .= $body->read(1024);

            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = trim(substr($buffer, 0, $pos));
                $buffer = substr($buffer, $pos + 1);

                if (!str_starts_with($line, 'data: '))
                    continue;

                $json = trim(substr($line, 6));
                if ($json === '' || $json === '[DONE]')
                    continue;

                $decoded = json_decode($json, true);

                // Check for API error in stream response
                if (isset($decoded['error'])) {
                    $errorMessage = $decoded['error']['message'] ?? 'Unknown Groq API error';
                    throw new \RuntimeException('Groq API error: ' . $errorMessage);
                }

                $text = $decoded['choices'][0]['delta']['content'] ?? '';

                if ($text !== '')
                    yield $text;
            }
        }

        // Process any remaining data in buffer
        $buffer = trim($buffer);
        if ($buffer && str_starts_with($buffer, 'data: ')) {
            $json = trim(substr($buffer, 6));
            if ($json !== '' && $json !== '[DONE]') {
                $decoded = json_decode($json, true);
                if (isset($decoded['choices'][0]['delta']['content'])) {
                    $text = $decoded['choices'][0]['delta']['content'];
                    if ($text !== '') {
                        yield $text;
                    }
                }
            }
        }
    }

    private function payload(string $prompt, array $options, bool $stream = false): array
    {
        return [
            'model' => $this->model,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 1024,
            'stream' => $stream,
        ];
    }
}