<?php

use App\Models\IncidentType;
use App\Models\Location;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/incidents', function () {
        $incidents = Auth::user()->incidents()->latest()->get();

        return view('incidents.index', ['incidents' => $incidents]);
    })->name('incidents.index');

    Route::get('/incidents/create', function () {
        return view('incidents.create', [
            'incidentTypes' => IncidentType::where('active', true)->orderBy('name')->get(),
            'locations' => Location::where('active', true)->orderBy('name')->get(),
        ]);
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

Route::middleware(['auth', 'beheerder'])->prefix('beheer')->name('beheer.')->group(function () {
    Route::get('/', function () {
        return view('beheer.index', [
            'incidentTypes' => IncidentType::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'roles' => UserRole::cases(),
        ]);
    })->name('index');

    Route::post('/incidenttypes', function (Request $request) {
        IncidentType::create($request->validate(['name' => ['required', 'string', 'max:255', 'unique:incident_types,name']]));

        return back()->with('status', 'Incidenttype toegevoegd.');
    })->name('incidenttypes.store');

    Route::patch('/incidenttypes/{incidentType}', function (Request $request, IncidentType $incidentType) {
        $incidentType->update($request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('incident_types', 'name')->ignore($incidentType)]]));

        return back()->with('status', 'Incidenttype bijgewerkt.');
    })->name('incidenttypes.update');

    Route::delete('/incidenttypes/{incidentType}', function (IncidentType $incidentType) {
        $incidentType->delete();

        return back()->with('status', 'Incidenttype verwijderd.');
    })->name('incidenttypes.destroy');

    Route::post('/locaties', function (Request $request) {
        Location::create($request->validate(['name' => ['required', 'string', 'max:255', 'unique:locations,name']]));

        return back()->with('status', 'Locatie toegevoegd.');
    })->name('locations.store');

    Route::patch('/locaties/{location}', function (Request $request, Location $location) {
        $location->update($request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('locations', 'name')->ignore($location)]]));

        return back()->with('status', 'Locatie bijgewerkt.');
    })->name('locations.update');

    Route::delete('/locaties/{location}', function (Location $location) {
        $location->delete();

        return back()->with('status', 'Locatie verwijderd.');
    })->name('locations.destroy');

    Route::patch('/gebruikers/{user}/rol', function (Request $request, User $user) {
        $validated = $request->validate(['role' => ['required', Rule::enum(UserRole::class)]]);
        $user->role = UserRole::from($validated['role']);
        $user->save();

        return back()->with('status', 'Gebruikersrol bijgewerkt.');
    })->name('users.role');
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

Route::middleware(['auth', 'beheerder'])->prefix('beheer')->name('beheer.')->group(function () {
    Route::get('/', function () {
        return view('beheer.index', [
            'incidentTypes' => IncidentType::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'roles' => UserRole::cases(),
        ]);
    })->name('index');

    Route::post('/incidenttypes', function (Request $request) {
        IncidentType::create($request->validate(['name' => ['required', 'string', 'max:255', 'unique:incident_types,name']]));

        return back()->with('status', 'Incidenttype toegevoegd.');
    })->name('incidenttypes.store');

    Route::patch('/incidenttypes/{incidentType}', function (Request $request, IncidentType $incidentType) {
        $incidentType->update($request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('incident_types', 'name')->ignore($incidentType)]]));

        return back()->with('status', 'Incidenttype bijgewerkt.');
    })->name('incidenttypes.update');

    Route::delete('/incidenttypes/{incidentType}', function (IncidentType $incidentType) {
        $incidentType->delete();

        return back()->with('status', 'Incidenttype verwijderd.');
    })->name('incidenttypes.destroy');

    Route::post('/locaties', function (Request $request) {
        Location::create($request->validate(['name' => ['required', 'string', 'max:255', 'unique:locations,name']]));

        return back()->with('status', 'Locatie toegevoegd.');
    })->name('locations.store');

    Route::patch('/locaties/{location}', function (Request $request, Location $location) {
        $location->update($request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('locations', 'name')->ignore($location)]]));

        return back()->with('status', 'Locatie bijgewerkt.');
    })->name('locations.update');

    Route::delete('/locaties/{location}', function (Location $location) {
        $location->delete();

        return back()->with('status', 'Locatie verwijderd.');
    })->name('locations.destroy');

    Route::patch('/gebruikers/{user}/rol', function (Request $request, User $user) {
        $validated = $request->validate(['role' => ['required', Rule::enum(UserRole::class)]]);
        $user->role = UserRole::from($validated['role']);
        $user->save();

        return back()->with('status', 'Gebruikersrol bijgewerkt.');
    })->name('users.role');
});
