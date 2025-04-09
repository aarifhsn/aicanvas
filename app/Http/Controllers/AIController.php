<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\HuggingFaceService;

class AIController extends Controller
{
    protected $huggingFaceService;

    public function __construct(HuggingFaceService $huggingFaceService)
    {
        $this->huggingFaceService = $huggingFaceService;
    }

    public function index()
    {
        return view('ai.index');
    }

    public function generateText(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
        ]);

        try {
            $result = $this->huggingFaceService->generateText(
                $request->prompt,
            );

            return response()->json([
                'success' => true,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            // Fallback code
            $result = $this->getFallbackResponse($request->prompt);

            return response()->json([
                'success' => true,
                'result' => $result,
                'fallback' => true
            ]);
        }
    }

    private function getFallbackResponse($prompt)
    {
        $responses = [
            "I'm sorry, but I'm having trouble processing that request right now.",
            "That's an interesting question. Let me think about that for a moment...",
            "I'd love to help with that, but my knowledge base is currently limited in this area.",
            "I understand you're asking about " . substr($prompt, 0, 20) . "... This is an area I'm still learning about."
        ];

        return $responses[array_rand($responses)];
    }
}
