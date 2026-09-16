<?php

namespace App\Http\Controllers;

use App\Models\IncidentType;
use App\Models\Location;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BeheerController extends Controller
{
    public function index(): View
    {
        return view('beheer.index', [
            'incidentTypes' => IncidentType::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'roles' => UserRole::cases(),
        ]);
    }

    public function storeIncidentType(Request $request): RedirectResponse
    {
        IncidentType::create($request->validate(['name' => ['required', 'string', 'max:255', 'unique:incident_types,name']]));

        return back()->with('status', 'Incidenttype toegevoegd.');
    }

    public function updateIncidentType(Request $request, IncidentType $incidentType): RedirectResponse
    {
        $incidentType->update($request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('incident_types', 'name')->ignore($incidentType)],
        ]));

        return back()->with('status', 'Incidenttype bijgewerkt.');
    }

    public function destroyIncidentType(IncidentType $incidentType): RedirectResponse
    {
        $incidentType->delete();

        return back()->with('status', 'Incidenttype verwijderd.');
    }

    public function storeLocation(Request $request): RedirectResponse
    {
        Location::create($request->validate(['name' => ['required', 'string', 'max:255', 'unique:locations,name']]));

        return back()->with('status', 'Locatie toegevoegd.');
    }

    public function updateLocation(Request $request, Location $location): RedirectResponse
    {
        $location->update($request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('locations', 'name')->ignore($location)],
        ]));

        return back()->with('status', 'Locatie bijgewerkt.');
    }

    public function destroyLocation(Location $location): RedirectResponse
    {
        $location->delete();

        return back()->with('status', 'Locatie verwijderd.');
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate(['role' => ['required', Rule::enum(UserRole::class)]]);
        $user->role = UserRole::from($validated['role']);
        $user->save();

        return back()->with('status', 'Gebruikersrol bijgewerkt.');
    }
}
