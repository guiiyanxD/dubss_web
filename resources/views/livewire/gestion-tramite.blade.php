<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Gestión de Trámites</h1>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <input type="text" wire:model.live.debounce.300ms="busqueda"
                    placeholder="Código, nombre, CI..."
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select wire:model.live="filtroEstado" class="w-full px-4 py-2 border rounded-lg">
                    <option value="">Todos</option>
                    @foreach($estados as $estado)
                        <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Convocatoria</label>
                <select wire:model.live="filtroConvocatoria" class="w-full px-4 py-2 border rounded-lg">
                    <option value="">Todas</option>
                    @foreach($convocatorias as $convocatoria)
                        <option value="{{ $convocatoria->id }}">{{ $convocatoria->codigo }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button wire:click="limpiarFiltros" class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Limpiar Filtros
                </button>
            </div>
        </div>
    </div>

    {{-- Tabla de trámites --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estudiante</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Convocatoria</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operador</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tramites as $tramite)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm">{{ $tramite->codigo }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $tramite->usuario->nombreCompleto() }}</div>
                            <div class="text-sm text-gray-500">CI: {{ $tramite->usuario->ci }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $tramite->convocatoria->codigo }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                style="background-color: {{ $tramite->estadoActual->color_hex }}20; color: {{ $tramite->estadoActual->color_hex }}">
                                {{ $tramite->estadoActual->nombre }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($tramite->operadorAsignado)
                                {{ $tramite->operadorAsignado->nombreCompleto() }}
                            @else
                                <button wire:click="asignarmeTraMite({{ $tramite->id }})"
                                    class="text-blue-600 hover:text-blue-800">
                                    Asignarme
                                </button>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $tramite->fecha_solicitud->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('tramites.detalle', $tramite) }}"
                                class="text-blue-600 hover:text-blue-900">
                                Ver Detalle
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No se encontraron trámites
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4 border-t">
            {{ $tramites->links() }}
        </div>
    </div>
</div>
