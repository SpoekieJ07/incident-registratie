<?php

namespace App\Http\Controllers;

use App\Models\IncidentType;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(Request $request): View
    {
        $incidents = $request->user()->incidents()->latest()->get();

        return view('incidents.index', ['incidents' => $incidents]);
    }

    public function create(): View
    {
        return view('incidents.create', [
            'incidentTypes' => IncidentType::where('active', true)->orderBy('name')->get(),
            'locations' => Location::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
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
    }
}
