<?php

use App\Services\AI\Contracts\AIProviderInterface;
use Tests\Fakes\FakeAIProvider;

it('generates text from a prompt', function () {
    $this->app->bind(AIProviderInterface::class, fn() => new FakeAIProvider('Hello from the fake model.'));

    $response = $this->postJson(route('ai.generate'), ['prompt' => 'Say hello']);

    $response->assertOk()->assertJson([
        'success' => true,
        'result' => 'Hello from the fake model.',
    ]);
});

it('rejects an empty prompt', function () {
    $this->postJson(route('ai.generate'), ['prompt' => ''])->assertStatus(422);
});

it('rejects a prompt over the max length', function () {
    $this->postJson(route('ai.generate'), ['prompt' => str_repeat('a', 1001)])->assertStatus(422);
});