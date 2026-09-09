<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/incidents', function () {
        $incidents = Auth::user()->incidents()->latest()->get();

        return view('incidents.index', ['incidents' => $incidents]);
    })->name('incidents.index');

    Route::get('/incidents/create', function () {
        return view('incidents.create');
    })->name('incidents.create');

    Route::post('/incidents', function (Request $request) {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'occurred_at' => ['required', 'date'],
            'type' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->incidents()->create([
            ...$validated,
            'status' => 'Open',
        ]);

        return redirect('/')->with('status', 'Je incident is succesvol gemeld.');
    })->name('incidents.store');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    return back()->withErrors([
        'email' => 'De opgegeven inloggegevens zijn onjuist.',
    ])->onlyInput('email');
})->name('login.post');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    Auth::login($user);

    return redirect()->route('login')->with('status', 'Registratie gelukt! Je kunt nu inloggen.');
})->name('register.post');
