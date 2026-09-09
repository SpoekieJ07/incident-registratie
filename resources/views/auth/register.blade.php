<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registreren</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-slate-800">Maak een account</h1>
            <p class="mt-2 text-sm text-slate-500">Vul hieronder je gegevens in</p>
        </div>

        @if (session('status'))
        <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Naam</label>
                <input id="name" name="name" type="text" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    placeholder="Jan Jansen">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">E-mail</label>
                <input id="email" name="email" type="email" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    placeholder="naam@email.com">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Wachtwoord</label>
                <input id="password" name="password" type="password" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    placeholder="••••••••">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Bevestig wachtwoord</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    placeholder="••••••••">
            </div>

            <button type="submit"
                class="w-full rounded-lg bg-blue-600 px-4 py-3 font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200">
                Registreren
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
            Heb je al een account?
            <a href="{{ url('/') }}" class="font-medium text-blue-600 hover:text-blue-500">Inloggen</a>
        </p>
    </div>
</body>

</html>