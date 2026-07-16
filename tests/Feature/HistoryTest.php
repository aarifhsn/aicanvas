<?php

use App\Models\Generation;
use App\Models\User;

it('redirects guests away from history', function () {
    $this->get(route('history.index'))->assertRedirect(route('login'));
});

it('shows only the authenticated users generations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Generation::factory()->for($user)->create(['prompt' => 'mine']);
    Generation::factory()->for($otherUser)->create(['prompt' => 'not mine']);

    $this->actingAs($user)->get(route('history.index'))
        ->assertOk()
        ->assertSee('mine')
        ->assertDontSee('not mine');
});

it('prevents deleting another users generation', function () {
    $user = User::factory()->create();
    $generation = Generation::factory()->for(User::factory()->create())->create();

    $this->actingAs($user)->delete(route('history.destroy', $generation))->assertForbidden();
    $this->assertDatabaseHas('generations', ['id' => $generation->id]);
});

it('allows deleting your own generation', function () {
    $user = User::factory()->create();
    $generation = Generation::factory()->for($user)->create();

    $this->actingAs($user)->delete(route('history.destroy', $generation))->assertRedirect();
    $this->assertDatabaseMissing('generations', ['id' => $generation->id]);
});