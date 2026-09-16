<?php

use App\Models\User;
use App\UserRole;

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('the login page is available', function () {
    $response = $this->get('/login');

    $response->assertStatus(200)
        ->assertSee('Inloggen')
        ->assertSee('Wachtwoord');
});

test('a user can register and be saved to the database', function () {
    $response = $this->post('/register', [
        'name' => 'Test Gebruiker',
        'email' => 'test@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertRedirect('/login');
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'name' => 'Test Gebruiker',
        'role' => UserRole::Melder->value,
    ]);
    $this->assertTrue(User::where('email', 'test@example.com')->exists());
});

test('users can have each supported role', function () {
    expect(User::factory()->user()->make()->role)->toBe(UserRole::User)
        ->and(User::factory()->make()->role)->toBe(UserRole::Melder)
        ->and(User::factory()->coordinator()->make()->role)->toBe(UserRole::Coordinator)
        ->and(User::factory()->beheerder()->make()->role)->toBe(UserRole::Beheerder);
});

test('an authenticated user can create an incident', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/incidents', [
            'title' => 'Netwerk uitval',
            'description' => 'Het netwerk in de kantine werkt niet.',
            'location' => 'Kantine',
            'occurred_at' => '2026-09-09T14:30',
            'type' => 'Infrastructuur',
        ]);

    $response->assertRedirect('/');
    $this->assertDatabaseHas('incidents', [
        'title' => 'Netwerk uitval',
        'location' => 'Kantine',
        'type' => 'Infrastructuur',
        'user_id' => $user->id,
    ]);
});

test('an authenticated user can view their own incidents and statuses', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $user->incidents()->create([
        'title' => 'Netwerk uitval',
        'description' => 'Het netwerk in de kantine werkt niet.',
        'location' => 'Kantine',
        'occurred_at' => '2026-09-09 14:30:00',
        'type' => 'Infrastructuur',
        'status' => 'In behandeling',
    ]);

    $otherUser->incidents()->create([
        'title' => 'Ander incident',
        'description' => 'Niet van deze gebruiker.',
        'location' => 'Magazijn',
        'occurred_at' => '2026-09-09 15:00:00',
        'type' => 'Veiligheid',
        'status' => 'Open',
    ]);

    $response = $this->actingAs($user)->get('/incidents');

    $response->assertOk()
        ->assertSee('Mijn meldingen')
        ->assertSee('Netwerk uitval')
        ->assertSee('In behandeling')
        ->assertDontSee('Ander incident');
});
