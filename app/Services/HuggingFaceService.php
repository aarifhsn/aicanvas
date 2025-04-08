<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HuggingFaceService
{
    protected $apiToken;
    protected $model;

    public function __construct()
    {
        $this->apiToken = env('HUGGINGFACE_API_TOKEN');
        $this->model = env('HUGGINGFACE_MODEL', 'google/flan-t5-small');
    }

    public function generateText($prompt)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/json',
        ])->post('https://api-inference.huggingface.co/models/' . $this->model, [
                    'inputs' => $prompt,
                    'parameters' => [
                        'max_length' => 100,
                        'temperature' => 0.7
                    ]
                ]);

        if ($response->successful()) {
            return $response->json(0)['generated_text'] ?? 'No response generated';
        }

        return 'Error: ' . ($response->json('error') ?? 'Unknown error');
    }
}