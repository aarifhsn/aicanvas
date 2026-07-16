<?php

it('lists available templates', function () {
    $this->getJson(route('ai.templates'))
        ->assertOk()
        ->assertJsonStructure(['templates' => ['blog_post' => ['name', 'fields', 'template']]]);
});

it('builds a prompt from a template', function () {
    $response = $this->postJson(route('ai.buildPrompt'), [
        'template' => 'email',
        'fields' => ['purpose' => 'Follow up on invoice', 'tone' => 'Formal'],
    ]);

    $response->assertOk()->assertJson(['success' => true]);
    expect($response->json('prompt'))->toContain('Follow up on invoice');
});

it('fails when a required template field is missing', function () {
    $this->postJson(route('ai.buildPrompt'), [
        'template' => 'email',
        'fields' => ['tone' => 'Formal'],
    ])->assertStatus(422);
});