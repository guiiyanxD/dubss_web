<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Convocatoria;
use Livewire\WithPagination;

class GestionConvocatorias extends Component
{
    use WithPagination;

    public $mostrarModal = false;
    public $convocatoriaId = null;

    // Campos del formulario
    public $nombre;
    public $codigo;
    public $tipo_beca;
    public $subtipo;
    public $descripcion;
    public $fecha_inicio;
    public $fecha_fin;
    public $fecha_limite_apelacion;
    public $activa = true;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'codigo' => 'required|string|max:50|unique:convocatorias,codigo',
        'tipo_beca' => 'required|in:socioeconomica,academica,extension',
        'subtipo' => 'required|string|max:100',
        'descripcion' => 'nullable|string',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'required|date|after:fecha_inicio',
        'fecha_limite_apelacion' => 'required|date|after:fecha_fin',
        'activa' => 'boolean',
    ];

    public function crear()
    {
        $this->resetForm();
        $this->convocatoriaId = null;
        $this->mostrarModal = true;
    }

    public function editar($convocatoriaId)
    {
        $convocatoria = Convocatoria::findOrFail($convocatoriaId);

        $this->convocatoriaId = $convocatoria->id;
        $this->nombre = $convocatoria->nombre;
        $this->codigo = $convocatoria->codigo;
        $this->tipo_beca = $convocatoria->tipo_beca;
        $this->subtipo = $convocatoria->subtipo;
        $this->descripcion = $convocatoria->descripcion;
        $this->fecha_inicio = $convocatoria->fecha_inicio->format('Y-m-d');
        $this->fecha_fin = $convocatoria->fecha_fin->format('Y-m-d');
        $this->fecha_limite_apelacion = $convocatoria->fecha_limite_apelacion->format('Y-m-d');
        $this->activa = $convocatoria->activa;

        $this->mostrarModal = true;
    }

    public function guardar()
    {
        if ($this->convocatoriaId) {
            $this->rules['codigo'] = 'required|string|max:50|unique:convocatorias,codigo,' . $this->convocatoriaId;
        }

        $this->validate();

        $data = [
            'nombre' => $this->nombre,
            'codigo' => $this->codigo,
            'tipo_beca' => $this->tipo_beca,
            'subtipo' => $this->subtipo,
            'descripcion' => $this->descripcion,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'fecha_limite_apelacion' => $this->fecha_limite_apelacion,
            'activa' => $this->activa,
        ];

        if ($this->convocatoriaId) {
            $convocatoria = Convocatoria::findOrFail($this->convocatoriaId);
            $convocatoria->update($data);
            $mensaje = 'Convocatoria actualizada correctamente';
        } else {
            $data['version'] = '1.0.0';
            Convocatoria::create($data);
            $mensaje = 'Convocatoria creada correctamente';
        }

        $this->mostrarModal = false;
        $this->resetForm();
        $this->dispatch('convocatoria-guardada', ['mensaje' => $mensaje]);
    }

    public function toggleActiva($convocatoriaId)
    {
        $convocatoria = Convocatoria::findOrFail($convocatoriaId);
        $convocatoria->update(['activa' => !$convocatoria->activa]);

        $this->dispatch('convocatoria-actualizada');
    }

    private function resetForm()
    {
        $this->nombre = '';
        $this->codigo = '';
        $this->tipo_beca = '';
        $this->subtipo = '';
        $this->descripcion = '';
        $this->fecha_inicio = '';
        $this->fecha_fin = '';
        $this->fecha_limite_apelacion = '';
        $this->activa = true;
    }

    public function render()
    {
        $convocatorias = Convocatoria::orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.gestion-convocatorias', [
            'convocatorias' => $convocatorias,
        ]);
    }
}
