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
        $this->model = env('HUGGINGFACE_MODEL', 'mistralai/Mixtral-8x7B-Instruct-v0.1');
    }

    public function generateText($prompt)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/json',
        ])->post('https://api-inference.huggingface.co/models/' . $this->model, [
                    'inputs' => $prompt,
                    'parameters' => [
                        'max_length' => 300,
                        'temperature' => 0.8,
                        'top_p' => 0.9
                    ]
                ]);

        if ($response->successful()) {
            $text = $response->json(0)['generated_text'] ?? 'No response generated';
            return $this->cleanResponse($text, $prompt);
        }

        return 'Error: ' . ($response->json('error') ?? 'Unknown error');
    }

    private function cleanResponse($text, $originalPrompt)
    {
        // Remove the original prompt if it appears in the response
        $text = str_replace($originalPrompt, '', $text);

        // Clean up any extra spaces
        $text = trim($text);

        // Format the text with proper line breaks
        $text = $this->formatText($text);

        return $text;
    }

    private function formatText($text)
    {
        // Replace emoji sequences with properly spaced emojis
        $text = preg_replace('/([!?.])\s*([🚀💡🎉])/', "$1\n\n$2", $text);

        // Add line breaks after sentences (but not breaking up abbreviations like PHP 8.1)
        $text = preg_replace('/([.!?])\s+(?=[A-Z])/', "$1\n\n", $text);

        // Add line breaks before code blocks
        $text = str_replace('```', "\n```", $text);

        // Add line breaks after paragraphs (double spaces often indicate paragraphs)
        $text = preg_replace('/(\S)\s{2,}(\S)/', "$1\n\n$2", $text);

        // Add line breaks for lists (lines starting with - or *)
        $text = preg_replace('/(\n|^)[ \t]*([*\-]) /', "$1\n$2 ", $text);

        // Format markdown headings with proper spacing
        $text = preg_replace('/(#+) (.+?)/', "\n$1 $2\n", $text);

        // Make sure there are no excessive line breaks
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return $text;
    }
}