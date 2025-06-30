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
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\AdminMiddleware;

// Rutas de autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth');

// Rutas del panel de administración
Route::middleware(['auth', AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard del administrador
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
    Route::resource('sesiones', SesionDefinicionController::class)->parameters([
        'sesiones' => 'sesion'
    ]);

    // Rutas adicionales para funcionalidades específicas
    Route::get('campeonatos/{campeonato}/pilotos', [CampeonatoController::class, 'managePilotos'])->name('campeonatos.pilotos');
    Route::post('campeonatos/{campeonato}/pilotos', [CampeonatoController::class, 'attachPiloto'])->name('campeonatos.pilotos.attach');
    Route::delete('campeonatos/{campeonato}/pilotos/{piloto}', [CampeonatoController::class, 'detachPiloto'])->name('campeonatos.pilotos.detach');
});
