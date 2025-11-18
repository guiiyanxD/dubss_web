<div class="space-y-6">
    {{-- Título --}}
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard DUBSS</h1>
        <button wire:click="cargarEstadisticas" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Actualizar
        </button>
    </div>

    {{-- Tarjetas de estadísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Trámites Hoy</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $estadisticas['total_tramites_hoy'] ?? 0 }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">En Revisión</p>
                    <p class="text-3xl font-bold text-amber-600">{{ $estadisticas['pendientes_revision'] ?? 0 }}</p>
                </div>
                <div class="bg-amber-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Aprobados Hoy</p>
                    <p class="text-3xl font-bold text-green-600">{{ $estadisticas['aprobados_hoy'] ?? 0 }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Tiempo Promedio</p>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($estadisticas['tiempo_promedio_procesamiento'] / 60, 1) }}h</p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Convocatorias activas --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Convocatorias Activas</h2>
        <div class="space-y-4">
            @forelse($convocatorias as $convocatoria)
                <div class="border rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-lg">{{ $convocatoria->nombre }}</h3>
                            <p class="text-sm text-gray-600">{{ $convocatoria->codigo }}</p>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $convocatoria->fecha_inicio->format('d/m/Y') }} - {{ $convocatoria->fecha_fin->format('d/m/Y') }}
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                            Activa
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">No hay convocatorias activas</p>
            @endforelse
        </div>
    </div>
</div>
