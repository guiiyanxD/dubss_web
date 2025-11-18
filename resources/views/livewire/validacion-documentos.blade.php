<div class="max-w-4xl mx-auto space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">Validación de Documentos</h1>

    @if($tramiteActual)
        <div class="bg-white rounded-lg shadow p-6">
            {{-- Info del trámite --}}
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-xl font-semibold">{{ $tramiteActual->codigo }}</h2>
                    <p class="text-gray-600">{{ $tramiteActual->usuario->nombreCompleto() }}</p>
                    <p class="text-sm text-gray-500">{{ $tramiteActual->convocatoria->nombre }}</p>
                </div>
                <button wire:click="saltarTramite" class="text-blue-600 hover:text-blue-800">
                    Saltar trámite
                </button>
            </div>

            {{-- Progreso --}}
            <div class="mb-6">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>Documento {{ $indiceActual + 1 }} de {{ count($documentos) }}</span>
                    <span>{{ number_format((($indiceActual + 1) / count($documentos)) * 100, 0) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all"
                        style="width: {{ (($indiceActual + 1) / count($documentos)) * 100 }}%"></div>
                </div>
            </div>

            {{-- Documento actual --}}
            @if(isset($documentos[$indiceActual]))
                <div class="border-2 border-gray-300 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-2">
                        {{ \App\Models\Requisito::find($documentos[$indiceActual]['requisito_id'])->nombre }}
                    </h3>
                    <p class="text-gray-600 mb-4">{{ $documentos[$indiceActual]['nombre_archivo'] }}</p>

                    {{-- Aquí iría un visor de PDF/imagen --}}
                    <div class="bg-gray-100 rounded-lg p-8 text-center mb-4">
                        <p class="text-gray-500">Vista previa del documento</p>
                        <p class="text-sm text-gray-400 mt-2">{{ $documentos[$indiceActual]['ruta_archivo'] }}</p>
                    </div>

                    {{-- Campo de observaciones para rechazo --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Observaciones (requerido si rechazas)
                        </label>
                        <textarea wire:model="observaciones.{{ $indiceActual }}"
                            class="w-full px-3 py-2 border rounded-lg"
                            rows="3"
                            placeholder="Ej: El documento no es legible, falta firma, etc."></textarea>
                        @error('observaciones.' . $indiceActual)
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Botones de acción --}}
                <div class="flex justify-between">
                    <button wire:click="anteriorDocumento"
                        @if($indiceActual === 0) disabled @endif
                        class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 disabled:opacity-50">
                        ← Anterior
                    </button>

                    <div class="flex space-x-3">
                        <button wire:click="rechazarDocumentoActual"
                            class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            ✕ Rechazar
                        </button>
                        <button wire:click="aprobarDocumentoActual"
                            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            ✓ Aprobar
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">¡Todo al día!</h3>
            <p class="text-gray-600">No hay trámites pendientes de validación en este momento.</p>
        </div>
    @endif
</div>
