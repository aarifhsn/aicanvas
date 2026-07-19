<?php

namespace App\Http\Controllers;

use App\Models\Generation;
use App\Services\AI\AIProviderManager;
use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\PromptTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AIController extends Controller
{
    public function __construct(protected AIProviderInterface $aiProvider)
    {
    }

    public function index()
    {
        return view('ai.index');
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

    public function generateText(Request $request)
    {
        $request->validate(['prompt' => 'required|string|max:1000']);

        try {
            $result = $this->aiProvider->generate($request->prompt);
            return response()->json(['success' => true, 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function generateStream(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'template' => 'nullable|string',
        ]);

        $prompt = $request->prompt;
        $templateKey = $request->template;
        $userId = $request->user()?->id;
        $provider = config('services.ai.default');

        return response()->stream(function () use ($prompt, $templateKey, $userId, $provider) {
            $fullText = '';

            try {
                foreach ($this->aiProvider->stream($prompt) as $chunk) {
                    $fullText .= $chunk;
                    echo 'data: ' . json_encode(['text' => $chunk]) . "\n\n";
                    if (ob_get_level() > 0)
                        ob_flush();
                    flush();
                }

                if ($userId && $fullText !== '') {
                    Generation::create([
                        'user_id' => $userId,
                        'provider' => $provider,
                        'template_key' => $templateKey,
                        'prompt' => $prompt,
                        'result' => $fullText,
                    ]);
                }

                echo 'data: ' . json_encode(['done' => true]) . "\n\n";
            } catch (\Exception $e) {
                echo 'data: ' . json_encode(['error' => $e->getMessage()]) . "\n\n";
            }

            if (ob_get_level() > 0)
                ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function compare(Request $request, AIProviderManager $manager)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'template' => 'nullable|string',
            'providers' => 'required|array|min:1|max:3',
            'providers.*' => Rule::in($manager->available()),
        ]);

        $userId = $request->user()?->id;
        $results = [];

        foreach ($request->providers as $providerName) {
            $start = microtime(true);
            try {
                $text = $manager->make($providerName)->generate($request->prompt);
                $latency = round((microtime(true) - $start) * 1000);

                $results[$providerName] = ['success' => true, 'text' => $text, 'latency_ms' => $latency];

                if ($userId) {
                    Generation::create([
                        'user_id' => $userId,
                        'provider' => $providerName,
                        'template_key' => $request->template,
                        'prompt' => $request->prompt,
                        'result' => $text,
                        'latency_ms' => $latency,
                    ]);
                }
            } catch (\Exception $e) {
                $results[$providerName] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        return response()->json(['success' => true, 'results' => $results]);
    }
}