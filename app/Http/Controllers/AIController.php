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
            $result = $this->huggingFaceService->generateText($request->prompt);

            return response()->json([
                'success' => true,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
