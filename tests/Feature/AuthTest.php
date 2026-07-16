<?php

use App\Models\User;

it('registers a new user and logs them in', function () {
    $response = $this->post(route('register'), [
        'name' => 'Arif',
        'email' => 'arif@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('ai.index'));
    $this->assertAuthenticated();
    expect(User::where('email', 'arif@example.com')->exists())->toBeTrue();
});

it('logs in with valid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $this->post(route('login'), ['email' => $user->email, 'password' => 'password123'])
        ->assertRedirect(route('ai.index'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid login credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs out an authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('logout'))->assertRedirect(route('ai.index'));

    $this->assertGuest();
});