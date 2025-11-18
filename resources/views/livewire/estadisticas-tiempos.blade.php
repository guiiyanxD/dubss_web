<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Estadísticas de Tiempos</h1>
        <button wire:click="cargarEstadisticas"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Actualizar
        </button>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Convocatoria</label>
                <select wire:model.live="convocatoriaSeleccionada"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Seleccionar convocatoria...</option>
                    @foreach($convocatorias as $conv)
                        <option value="{{ $conv->id }}">{{ $conv->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                <select wire:model.live="rangoFechas"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="7">Últimos 7 días</option>
                    <option value="30">Últimos 30 días</option>
                    <option value="90">Últimos 90 días</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Estadísticas --}}
    @if($convocatoriaSeleccionada && !empty($estadisticas))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($estadisticas as $nombreEstado => $stats)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $nombreEstado }}</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Promedio:</span>
                            <span class="text-lg font-bold text-blue-600">
                                {{ number_format($stats['promedio_minutos'] / 60, 1) }} horas
                            </span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Mínimo:</span>
                            <span class="text-sm text-gray-900">
                                {{ number_format($stats['minimo_minutos'] / 60, 1) }} horas
                            </span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Máximo:</span>
                            <span class="text-sm text-gray-900">
                                {{ number_format($stats['maximo_minutos'] / 60, 1) }} horas
                            </span>
                        </div>

                        <div class="pt-3 border-t border-gray-200">
                            <span class="text-xs text-gray-500">
                                Basado en {{ $stats['total_tramites'] }} trámites
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay datos disponibles</h3>
            <p class="mt-1 text-sm text-gray-500">Selecciona una convocatoria para ver las estadísticas</p>
        </div>
    @endif
</div>
