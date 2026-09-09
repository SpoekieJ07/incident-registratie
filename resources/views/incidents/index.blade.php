<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mijn meldingen | {{ config('app.name', 'IncidentDesk') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="incident-page">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 24px 24px 72px;">
        <x-layout />
    </div>

    <div class="incident-shell">
        <div class="incident-header">
            <span class="eyebrow">Mijn meldingen</span>
            <h1>Overzicht van jouw incidenten</h1>
            <p>Hier kun je je eigen meldingen en de huidige status bekijken.</p>
        </div>

        @if ($incidents->isEmpty())
        <div class="auth-alert auth-alert-success">
            Je hebt nog geen meldingen gemaakt.
        </div>
        @else
        <div class="incident-list-panel">
            @foreach ($incidents as $incident)
            <article class="incident-card">
                <div class="incident-card-header">
                    <div>
                        <h2>{{ $incident->title }}</h2>
                        <p>{{ $incident->location }} • {{ $incident->occurred_at->format('d-m-Y H:i') }}</p>
                    </div>
                    <span class="status-pill status-{{ strtolower(str_replace(' ', '-', $incident->status)) }}">{{ $incident->status }}</span>
                </div>

                <p class="incident-description">{{ $incident->description }}</p>

                <div class="incident-meta">
                    <span><strong>Type:</strong> {{ $incident->type }}</span>
                    <span><strong>Status:</strong> {{ $incident->status }}</span>
                </div>
            </article>
            @endforeach
        </div>
        @endif

        <div class="incident-actions">
            <a class="btn btn-primary" href="{{ route('incidents.create') }}">Nieuwe melding</a>
            <a class="btn btn-outline" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Uitloggen</a>
        </div>

        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">
            @csrf
        </form>
    </div>
</body>

</html>