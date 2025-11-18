<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Livewire\Dashboard;
use App\Livewire\GestionConvocatorias;
use App\Livewire\GestionTramites;
use App\Livewire\DetalleTramite;
use App\Livewire\ValidacionDocumentos;
use App\Livewire\EstadisticasTiempos;
use App\Livewire\GestionTurnos;

Route::get('/', function () {
    return view('welcome');
});

/* Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/tramites', GestionTramites::class)->name('tramites.index');
    Route::get('/tramites/{tramite}', DetalleTramite::class)->name('tramites.detalle');
    Route::get('/validar-documentos', ValidacionDocumentos::class)->name('validar.documentos');
    Route::get('/convocatorias', GestionConvocatorias::class)->name('convocatorias');
    Route::get('/estadisticas', EstadisticasTiempos::class)->name('estadisticas');
    Route::get('/turnos', GestionTurnos::class)->name('turnos');
});

require __DIR__.'/auth.php';
