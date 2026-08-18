<!DOCTYPE html>
<html lang="fr" class="{{ (auth()->user()?->theme_settings['mode'] ?? 'light') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>

    {{-- Injection des couleurs perso de l'utilisateur en variables CSS --}}
    <style>
        :root {
            --theme-color: {{ auth()->user()->theme_settings['primary'] ?? auth()->user()->theme_color ?? '#6366f1' }};
            --theme-secondary: {{ auth()->user()->theme_settings['secondary'] ?? '#F5F2E9' }};
            --theme-accent: {{ auth()->user()->theme_settings['accent'] ?? '#E8C4A0' }};
        }
        .btn-primary {
            background: var(--theme-color); color: white; padding: .5rem 1rem;
            border-radius: .5rem; font-weight: 500;
        }
        .btn-secondary {
            background: var(--theme-secondary); padding: .5rem 1rem;
            border-radius: .5rem; font-weight: 500;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 dark:bg-gray-900 dark:text-gray-100 min-h-screen">

    <nav class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ url('/') }}" class="font-bold" style="color: var(--theme-color)">Cairn</a>

            <div class="flex items-center gap-4 text-sm">
                {{-- Nav generee dynamiquement: uniquement les apps auxquelles l'utilisateur a acces --}}
                @if (auth()->check())
                    @foreach (auth()->user()->apps as $appModule)
                        @php($instance = $appModule->moduleInstance())
                        @if ($instance)
                            <a href="{{ route($instance->entryRoute()) }}" class="hover:underline">{{ $appModule->name }}</a>
                        @endif
                    @endforeach

                    <a href="{{ route('profile.show') }}" class="hover:underline">Mon profil</a>

                    @if (auth()->user()->is_admin)
                        <a href="{{ url('/admin') }}" class="hover:underline font-medium">Admin</a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="hover:underline">Déconnexion</button>
                    </form>
                @endif
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    @livewireScripts
    @stack('scripts')
</body>
</html>
