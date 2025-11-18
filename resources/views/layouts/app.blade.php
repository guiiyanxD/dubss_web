<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DUBSS') }} - Panel Administrativo</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen">
        {{-- Navbar --}}
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}" class="text-2xl font-bold text-blue-600">DUBSS</a>
                        </div>

                        {{-- Desktop Menu --}}
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition
                                      {{ request()->routeIs('dashboard') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                                Dashboard
                            </a>

                            <a href="{{ route('tramites.index') }}"
                               class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition
                                      {{ request()->routeIs('tramites.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                                Trámites
                            </a>

                            @if(auth()->check() && auth()->user()->esOperador())
                                <a href="{{ route('validar.documentos') }}"
                                   class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition
                                          {{ request()->routeIs('validar.documentos') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                                    Validar Documentos
                                </a>

                                <a href="{{ route('turnos') }}"
                                   class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition
                                          {{ request()->routeIs('turnos') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                                    Turnos
                                </a>

                                <a href="{{ route('convocatorias') }}"
                                   class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition
                                          {{ request()->routeIs('convocatorias') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                                    Convocatorias
                                </a>

                                <a href="{{ route('estadisticas') }}"
                                   class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition
                                          {{ request()->routeIs('estadisticas') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                                    Estadísticas
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- User Dropdown --}}
                    <div class="hidden sm:flex sm:items-center" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none transition">
                            <span>{{ auth()->user()->nombreCompleto() }}</span>
                            <svg class="ml-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-4 top-16 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ ucfirst(auth()->user()->rol) }}
                                        </span>
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                        Cerrar Sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Mobile menu button --}}
                    <div class="flex items-center sm:hidden" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        {{-- Mobile menu --}}
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute top-16 left-0 right-0 bg-white shadow-lg z-50">
                            <div class="pt-2 pb-3 space-y-1">
                                <a href="{{ route('dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('dashboard') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50' }}">Dashboard</a>

                                <a href="{{ route('tramites.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('tramites.*') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50' }}">Trámites</a>

                                @if(auth()->check() && auth()->user()->esOperador())
                                    <a href="{{ route('validar.documentos') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium border-transparent text-gray-600 hover:bg-gray-50">Validar Documentos</a>
                                    <a href="{{ route('turnos') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium border-transparent text-gray-600 hover:bg-gray-50">Turnos</a>
                                    <a href="{{ route('convocatorias') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium border-transparent text-gray-600 hover:bg-gray-50">Convocatorias</a>
                                    <a href="{{ route('estadisticas') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium border-transparent text-gray-600 hover:bg-gray-50">Estadísticas</a>
                                @endif
                            </div>

                            <div class="pt-4 pb-3 border-t border-gray-200">
                                <div class="px-4">
                                    <p class="text-base font-medium text-gray-800">{{ auth()->user()->nombreCompleto() }}</p>
                                    <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="mt-3">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Cerrar Sesión</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        {{-- Content --}}
        <main class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                @if (session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
