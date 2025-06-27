<?php
// routes/web.php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CampeonatoController;
use App\Http\Controllers\PilotoController;
use App\Http\Controllers\CircuitoController;
use App\Http\Controllers\FechaController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\SesionDefinicionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas del panel de administración
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard principal
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Gestión de Campeonatos
    Route::resource('campeonatos', CampeonatoController::class);

    // Gestión de Pilotos
    Route::resource('pilotos', PilotoController::class);

    // Gestión de Circuitos
    Route::resource('circuitos', CircuitoController::class);

    // Gestión de Fechas
    Route::resource('fechas', FechaController::class);

    // Gestión de Horarios
    Route::resource('horarios', HorarioController::class);

    // Gestión de Sesiones
    Route::resource('sesiones', SesionDefinicionController::class);

    // Rutas adicionales para funcionalidades específicas
    Route::get('campeonatos/{campeonato}/pilotos', [CampeonatoController::class, 'managePilotos'])->name('campeonatos.pilotos');
    Route::post('campeonatos/{campeonato}/pilotos', [CampeonatoController::class, 'attachPiloto'])->name('campeonatos.pilotos.attach');
    Route::delete('campeonatos/{campeonato}/pilotos/{piloto}', [CampeonatoController::class, 'detachPiloto'])->name('campeonatos.pilotos.detach');
});

require __DIR__ . '/auth.php';
