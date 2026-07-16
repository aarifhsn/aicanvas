<?php

namespace App\Http\Controllers;

use App\Services\AI\Contracts\AIProviderInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\AI\AIProviderManager;
use Illuminate\Validation\Rule;
use App\Services\AI\PromptTemplateService;


class AIController extends Controller
{
    public function __construct(protected AIProviderInterface $aiProvider)
    {
    }

    public function index()
    {
        return view('ai.index');
    }

    public function generateText(Request $request)
    {
        $request->validate(['prompt' => 'required|string|max:1000']);

        try {
            $result = $this->aiProvider->generate($request->prompt);

            return response()->json(['success' => true, 'result' => $result]);
        } catch (\Exception $e) {
            Log::error('AI generation error', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function generateStream(Request $request)
    {
        $request->validate(['prompt' => 'required|string|max:1000']);
        $prompt = $request->prompt;

        return response()->stream(function () use ($prompt) {
            try {
                foreach ($this->aiProvider->stream($prompt) as $chunk) {
                    echo 'data: ' . json_encode(['text' => $chunk]) . "\n\n";
                    if (ob_get_level() > 0)
                        ob_flush();
                    flush();
                }
                echo 'data: ' . json_encode(['done' => true]) . "\n\n";
            } catch (\Exception $e) {
                Log::error('AI stream error', ['message' => $e->getMessage()]);
                echo 'data: ' . json_encode(['error' => $e->getMessage()]) . "\n\n";
            }
            if (ob_get_level() > 0)
                ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no', // needed if you're behind nginx on cPanel
        ]);
    }

    public function compare(Request $request, AIProviderManager $manager)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'providers' => 'required|array|min:1|max:3',
            'providers.*' => Rule::in($manager->available()),
        ]);

        $results = [];

        foreach ($request->providers as $providerName) {
            $start = microtime(true);
            try {
                $text = $manager->make($providerName)->generate($request->prompt);
                $results[$providerName] = [
                    'success' => true,
                    'text' => $text,
                    'latency_ms' => round((microtime(true) - $start) * 1000),
                ];
            } catch (\Exception $e) {
                Log::error("Compare failed for {$providerName}", ['message' => $e->getMessage()]);
                $results[$providerName] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        return response()->json(['success' => true, 'results' => $results]);
    }

    public function templates(PromptTemplateService $templates)
    {
        return response()->json(['templates' => $templates->all()]);
    }

    public function buildPrompt(Request $request, PromptTemplateService $templates)
    {
        $request->validate([
            'template' => 'required|string',
            'fields' => 'nullable|array',
        ]);

        try {
            $prompt = $templates->build($request->template, $request->fields ?? []);
            return response()->json(['success' => true, 'prompt' => $prompt]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }
}