<?php

use App\Services\AI\PromptTemplateService;

it('builds a prompt by replacing placeholders', function () {
    $prompt = (new PromptTemplateService())->build('summarizer', [
        'text' => 'Long article content here.',
        'length' => 'Bullet points',
    ]);

    expect($prompt)
        ->toContain('Long article content here.')
        ->toContain('Bullet points');
});

it('throws when a required field is missing', function () {
    (new PromptTemplateService())->build('summarizer', ['length' => 'Bullet points']);
})->throws(InvalidArgumentException::class);

it('throws for an unknown template key', function () {
    (new PromptTemplateService())->get('not-a-template');
})->throws(InvalidArgumentException::class);