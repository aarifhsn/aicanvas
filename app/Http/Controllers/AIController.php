<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI;

class AIController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(env('OPENAI_API_KEY'));
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
            $response = $this->client->completions()->create([
                'model' => 'gpt-3.5-turbo-instruct',
                'prompt' => $request->prompt,
                'max_tokens' => 150,
                'temperature' => 0.7,
            ]);

            return response()->json([
                'success' => true,
                'result' => $response->choices[0]->text
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
