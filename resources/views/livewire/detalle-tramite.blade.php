<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Trámite {{ $tramite->codigo }}</h1>
                <p class="text-gray-600 mt-1">{{ $tramite->convocatoria->nombre }}</p>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-semibold"
                style="background-color: {{ $tramite->estadoActual->color_hex }}20; color: {{ $tramite->estadoActual->color_hex }}">
                {{ $tramite->estadoActual->nombre }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Información del estudiante --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Información del Estudiante</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm text-gray-600">Nombre Completo</dt>
                    <dd class="font-medium">{{ $tramite->usuario->nombreCompleto() }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">CI</dt>
                    <dd class="font-medium">{{ $tramite->usuario->ci }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Email</dt>
                    <dd class="font-medium">{{ $tramite->usuario->email }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Teléfono</dt>
                    <dd class="font-medium">{{ $tramite->usuario->telefono }}</dd>
                </div>
            </dl>
        </div>

        {{-- Documentos --}}
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Documentos ({{ $tramite->documentos->count() }})</h2>
                <button wire:click="abrirModalCambioEstado"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Cambiar Estado
                </button>
            </div>

            <div class="space-y-4">
                @foreach($tramite->documentos as $documento)
                    <div class="border rounded-lg p-4 {{ $documentoSeleccionado === $documento->id ? 'ring-2 ring-blue-500' : '' }}">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-medium">{{ $documento->requisito->nombre }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ $documento->nombre_archivo }}</p>

                                @if($documento->estado === 'rechazado' && $documento->observaciones)
                                    <p class="text-sm text-red-600 mt-2">
                                        <strong>Observación:</strong> {{ $documento->observaciones }}
                                    </p>
                                @endif

                                @if($documento->validado_por)
                                    <p class="text-xs text-gray-500 mt-2">
                                        Validado por {{ $documento->validador->nombreCompleto() }}
                                        el {{ $documento->validado_en->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex items-center space-x-2 ml-4">
                                @if($documento->estado === 'pendiente')
                                    <button wire:click="aprobarDocumento({{ $documento->id }})"
                                        class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                        Aprobar
                                    </button>
                                    <button wire:click="seleccionarDocumento({{ $documento->id }})"
                                        class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                        Rechazar
                                    </button>
                                @else
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                                        {{ $documento->estado === 'aprobado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($documento->estado) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($documentoSeleccionado === $documento->id)
                            <div class="mt-4 pt-4 border-t">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones *</label>
                                <textarea wire:model="observacionDocumento"
                                    class="w-full px-3 py-2 border rounded-lg"
                                    rows="3"
                                    placeholder="Especifica el motivo del rechazo..."></textarea>
                                @error('observacionDocumento')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror

                                <div class="flex space-x-2 mt-3">
                                    <button wire:click="rechazarDocumento({{ $documento->id }})"
                                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                        Confirmar Rechazo
                                    </button>
                                    <button wire:click="$set('documentoSeleccionado', null)"
                                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Historial --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Historial de Cambios</h2>
        <div class="space-y-4">
            @foreach($tramite->historial as $registro)
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center"
                            style="background-color: {{ $registro->estadoNuevo->color_hex }}20">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $registro->estadoNuevo->color_hex }}"></div>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="font-medium">{{ $registro->estadoNuevo->nombre }}</p>
                        <p class="text-sm text-gray-600">
                            Por {{ $registro->usuario->nombreCompleto() }} -
                            {{ $registro->fecha_cambio->format('d/m/Y H:i') }}
                        </p>
                        @if($registro->comentario)
                            <p class="text-sm text-gray-700 mt-1">{{ $registro->comentario }}</p>
                        @endif
                        @if($registro->tiempo_en_estado_anterior_min)
                            <p class="text-xs text-gray-500 mt-1">
                                Tiempo en estado anterior: {{ number_format($registro->tiempo_en_estado_anterior_min / 60, 1) }} horas
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Modal cambio de estado --}}
    @if($mostrarModalCambioEstado)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <h3 class="text-lg font-bold mb-4">Cambiar Estado del Trámite</h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nuevo Estado *</label>
                    <select wire:model="nuevoEstado" class="w-full px-3 py-2 border rounded-lg">
                        <option value="">Seleccionar...</option>
                        @foreach($this->estadosPosibles as $estado)
                            <option value="{{ $estado->codigo }}">{{ $estado->nombre }}</option>
                        @endforeach
                    </select>
                    @error('nuevoEstado') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Comentario</label>
                    <textarea wire:model="comentario" class="w-full px-3 py-2 border rounded-lg" rows="3"></textarea>
                    @error('comentario') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('mostrarModalCambioEstado', false)"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        Cancelar
                    </button>
                    <button wire:click="cambiarEstado"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
