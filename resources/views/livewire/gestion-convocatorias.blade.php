<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Gestión de Convocatorias</h1>
        <button wire:click="crear"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Nueva Convocatoria
        </button>
    </div>

    {{-- Tabla de convocatorias --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fechas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($convocatorias as $convocatoria)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $convocatoria->nombre }}</div>
                            <div class="text-sm text-gray-500">{{ $convocatoria->subtipo }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm">{{ $convocatoria->codigo }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $convocatoria->tipo_beca === 'socioeconomica' ? 'bg-blue-100 text-blue-800' :
                                           ($convocatoria->tipo_beca === 'academica' ? 'bg-purple-100 text-purple-800' :
                                           'bg-green-100 text-green-800') }}">
                                {{ ucfirst($convocatoria->tipo_beca) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div>{{ $convocatoria->fecha_inicio->format('d/m/Y') }} - {{ $convocatoria->fecha_fin->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-500">Apelación: {{ $convocatoria->fecha_limite_apelacion->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button wire:click="toggleActiva({{ $convocatoria->id }})"
                                    class="px-2 py-1 text-xs font-semibold rounded-full
                                           {{ $convocatoria->activa ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $convocatoria->activa ? 'Activa' : 'Inactiva' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button wire:click="editar({{ $convocatoria->id }})"
                                    class="text-blue-600 hover:text-blue-900 mr-3">
                                Editar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No hay convocatorias registradas
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4 border-t">
            {{ $convocatorias->links() }}
        </div>
    </div>

    {{-- Modal crear/editar --}}
    @if($mostrarModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
                <h3 class="text-lg font-bold mb-4">
                    {{ $convocatoriaId ? 'Editar' : 'Nueva' }} Convocatoria
                </h3>

                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                            <input type="text" wire:model="nombre"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('nombre') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Código *</label>
                            <input type="text" wire:model="codigo"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('codigo') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo Beca *</label>
                            <select wire:model="tipo_beca"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Seleccionar...</option>
                                <option value="socioeconomica">Socioeconómica</option>
                                <option value="academica">Académica</option>
                                <option value="extension">Extensión</option>
                            </select>
                            @error('tipo_beca') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subtipo *</label>
                            <input type="text" wire:model="subtipo" placeholder="Ej: estudio, albergue, alimentación"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('subtipo') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio *</label>
                            <input type="date" wire:model="fecha_inicio"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('fecha_inicio') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin *</label>
                            <input type="date" wire:model="fecha_fin"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('fecha_fin') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Límite Apelación *</label>
                            <input type="date" wire:model="fecha_limite_apelacion"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('fecha_limite_apelacion') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                            <textarea wire:model="descripcion" rows="3"
                                      class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                            @error('descripcion') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="activa" class="rounded">
                                <span class="ml-2 text-sm text-gray-700">Convocatoria activa</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2 mt-6">
                        <button type="button"
                                wire:click="$set('mostrarModal', false)"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
