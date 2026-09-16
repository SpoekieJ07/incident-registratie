<?php

use App\Models\IncidentType;
use App\Models\Location;
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

test('only a beheerder can manage catalogs and user roles', function () {
    $beheerder = User::factory()->beheerder()->create();
    $user = User::factory()->create();
    $typeName = 'Veiligheid '.uniqid();
    $locationName = 'Hoofdkantoor '.uniqid();

    $this->actingAs($user)->get('/beheer')->assertForbidden();

    $this->actingAs($beheerder)->post('/beheer/incidenttypes', ['name' => $typeName])->assertRedirect();
    $this->actingAs($beheerder)->post('/beheer/locaties', ['name' => $locationName])->assertRedirect();

    $incidentType = IncidentType::where('name', $typeName)->firstOrFail();
    $location = Location::where('name', $locationName)->firstOrFail();
    $this->assertDatabaseHas('incident_types', ['name' => $typeName]);
    $this->assertDatabaseHas('locations', ['name' => $locationName]);

    $this->actingAs($beheerder)->patch("/beheer/incidenttypes/{$incidentType->id}", ['name' => $typeName.' aangepast'])->assertRedirect();
    $this->actingAs($beheerder)->patch("/beheer/locaties/{$location->id}", ['name' => $locationName.' aangepast'])->assertRedirect();
    $this->assertDatabaseHas('incident_types', ['name' => $typeName.' aangepast']);
    $this->assertDatabaseHas('locations', ['name' => $locationName.' aangepast']);

    $this->actingAs($beheerder)->delete("/beheer/incidenttypes/{$incidentType->id}")->assertRedirect();
    $this->actingAs($beheerder)->delete("/beheer/locaties/{$location->id}")->assertRedirect();
    $this->assertDatabaseMissing('incident_types', ['id' => $incidentType->id]);
    $this->assertDatabaseMissing('locations', ['id' => $location->id]);

    $this->actingAs($beheerder)->patch("/beheer/gebruikers/{$user->id}/rol", [
        'role' => UserRole::Coordinator->value,
    ])->assertRedirect();

    expect($user->refresh()->role)->toBe(UserRole::Coordinator);
});

test('a melder must provide all required incident fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/incidents', [
        'title' => '',
        'description' => '',
        'location' => '',
        'occurred_at' => '',
        'type' => '',
    ]);

    $response->assertSessionHasErrors(['title', 'description', 'location', 'occurred_at', 'type']);
    $this->assertDatabaseMissing('incidents', ['user_id' => $user->id]);
});
