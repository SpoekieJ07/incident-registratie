<?php

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
