<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Gestión de Turnos</h1>
        <button wire:click="cargarEstadisticas"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Actualizar
        </button>
    </div>

    {{-- Estadísticas del día --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Total Slots</p>
            <p class="text-2xl font-bold text-gray-900">{{ $estadisticas['total_slots'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Ocupados</p>
            <p class="text-2xl font-bold text-amber-600">{{ $estadisticas['slots_ocupados'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Atendidos</p>
            <p class="text-2xl font-bold text-green-600">{{ $estadisticas['turnos_atendidos'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Tasa Ocupación</p>
            <p class="text-2xl font-bold text-purple-600">{{ $estadisticas['tasa_ocupacion'] ?? 0 }}%</p>
        </div>
    </div>

    {{-- Selector de fecha y filtros --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Navegación de fecha --}}
            <div class="flex items-center space-x-2">
                <button wire:click="cambiarFecha('anterior')"
                        class="p-2 border rounded hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <input type="date"
                       wire:model.live="fechaSeleccionada"
                       class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                <button wire:click="cambiarFecha('siguiente')"
                        class="p-2 border rounded hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            {{-- Filtro por estado --}}
            <div>
                <select wire:model.live="filtroEstado"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los estados</option>
                    <option value="reservado">Reservado</option>
                    <option value="atendido">Atendido</option>
                    <option value="cancelado">Cancelado</option>
                    <option value="vencido">Vencido</option>
                </select>
            </div>

            {{-- Botón hoy --}}
            <div>
                <button wire:click="$set('fechaSeleccionada', '{{ today()->format('Y-m-d') }}')"
                        class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Hoy
                </button>
            </div>
        </div>
    </div>

    {{-- Vista de slots (horarios disponibles) --}}
    @if($slotsDisponibles->isNotEmpty())
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Horarios Disponibles</h2>
            <div class="grid grid-cols-4 md:grid-cols-8 lg:grid-cols-12 gap-2">
                @foreach($slotsDisponibles as $slot)
                    <div class="text-center p-2 rounded border
                                {{ $slot['disponible'] && !$slot['en_pasado'] ? 'bg-green-50 border-green-300 text-green-700' :
                                   ($slot['en_pasado'] ? 'bg-gray-100 border-gray-300 text-gray-400' :
                                   'bg-red-50 border-red-300 text-red-700') }}">
                        <p class="text-xs font-medium">{{ $slot['hora_inicio'] }}</p>
                        @if($slot['disponible'] && !$slot['en_pasado'])
                            <p class="text-xs">✓</p>
                        @elseif($slot['en_pasado'])
                            <p class="text-xs">-</p>
                        @else
                            <p class="text-xs">✗</p>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="flex items-center space-x-4 mt-4 text-sm">
                <span class="flex items-center">
                    <span class="w-3 h-3 bg-green-50 border border-green-300 rounded mr-2"></span>
                    Disponible
                </span>
                <span class="flex items-center">
                    <span class="w-3 h-3 bg-red-50 border border-red-300 rounded mr-2"></span>
                    Ocupado
                </span>
                <span class="flex items-center">
                    <span class="w-3 h-3 bg-gray-100 border border-gray-300 rounded mr-2"></span>
                    Pasado
                </span>
            </div>
        </div>
    @endif

    {{-- Lista de turnos --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Horario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trámite</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Atendido por</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($turnos as $turno)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm font-medium">{{ $turno->codigo }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            {{ $turno->hora_inicio }} - {{ $turno->hora_fin }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $turno->usuario->nombreCompleto() }}</div>
                            <div class="text-sm text-gray-500">CI: {{ $turno->usuario->ci }}</div>
                            <div class="text-sm text-gray-500">{{ $turno->usuario->telefono }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $turno->estado === 'reservado' ? 'bg-yellow-100 text-yellow-800' :
                                           ($turno->estado === 'atendido' ? 'bg-green-100 text-green-800' :
                                           ($turno->estado === 'cancelado' ? 'bg-red-100 text-red-800' :
                                           'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($turno->estado) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($turno->tramites->isNotEmpty())
                                @foreach($turno->tramites as $tramite)
                                    <a href="{{ route('tramites.detalle', $tramite) }}"
                                       class="text-blue-600 hover:text-blue-800 block">
                                        {{ $tramite->codigo }}
                                    </a>
                                @endforeach
                            @else
                                <span class="text-gray-400">Sin trámite</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($turno->atendidoPor)
                                <div>{{ $turno->atendidoPor->nombreCompleto() }}</div>
                                <div class="text-xs text-gray-500">{{ $turno->atendido_en->format('H:i') }}</div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($turno->estado === 'reservado')
                                <div class="flex space-x-2">
                                    <button wire:click="atenderTurno({{ $turno->id }})"
                                            class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                                        Atender
                                    </button>
                                    <button wire:click="liberarTurno({{ $turno->id }})"
                                            class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                        Liberar
                                    </button>
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No hay turnos para esta fecha
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4 border-t">
            {{ $turnos->links() }}
        </div>
    </div>
</div>
