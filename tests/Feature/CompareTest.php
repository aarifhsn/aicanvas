<?php

use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\GroqProvider;
use Tests\Fakes\FakeAIProvider;

it('compares multiple providers and returns latency', function () {
    $this->app->bind(GeminiProvider::class, fn() => new FakeAIProvider('Gemini says hi.'));
    $this->app->bind(GroqProvider::class, fn() => new FakeAIProvider('Groq says hi.'));

    $response = $this->postJson(route('ai.compare'), [
        'prompt' => 'Say hi',
        'providers' => ['gemini', 'groq'],
    ]);

    $response->assertOk()
        ->assertJsonPath('results.gemini.text', 'Gemini says hi.')
        ->assertJsonPath('results.groq.text', 'Groq says hi.')
        ->assertJsonPath('results.gemini.success', true);
});

it('rejects an unknown provider', function () {
    $this->postJson(route('ai.compare'), [
        'prompt' => 'Say hi',
        'providers' => ['not-a-real-provider'],
    ])->assertStatus(422);
});

it('rejects more than 3 providers', function () {
    $this->postJson(route('ai.compare'), [
        'prompt' => 'Say hi',
        'providers' => ['gemini', 'groq', 'gemini', 'groq'],
    ])->assertStatus(422);
});