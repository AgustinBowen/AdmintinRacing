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
use App\Http\Controllers\ResultadoSesionController;
use Illuminate\Support\Facades\Route;
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
    Route::get('campeonatos/{campeonato}/standings',                       [CampeonatoController::class, 'standings'])->name('campeonatos.standings');
    Route::get('campeonatos/{campeonato}/scoring',                         [CampeonatoController::class, 'scoring'])->name('campeonatos.scoring');
    Route::patch('campeonatos/{campeonato}/scoring/{sistemaPuntaje}',      [CampeonatoController::class, 'updateScoring'])->name('campeonatos.scoring.update');
    Route::post('campeonatos/{campeonato}/scoring/add',                    [CampeonatoController::class, 'addScoring'])->name('campeonatos.scoring.add');
    Route::delete('campeonatos/{campeonato}/scoring/{sistemaPuntaje}/del', [CampeonatoController::class, 'deleteScoring'])->name('campeonatos.scoring.delete');
    Route::post('campeonatos/{campeonato}/scoring/reset',                  [CampeonatoController::class, 'resetScoring'])->name('campeonatos.scoring.reset');
    Route::post('campeonatos/{campeonato}/sync',                           [CampeonatoController::class, 'syncStandings'])->name('campeonatos.sync');
    Route::resource('campeonatos', CampeonatoController::class);

    // Gestión de Pilotos
    Route::get('pilotos/import', [PilotoController::class, 'importForm'])->name('pilotos.import.form');
    Route::post('pilotos/import/preview', [PilotoController::class, 'importPreview'])->name('pilotos.import.preview');
    Route::post('pilotos/import/store', [PilotoController::class, 'storeImport'])->name('pilotos.import.store');
    Route::post('pilotos/quick-store', [PilotoController::class, 'quickStore'])->name('pilotos.quick-store');
    Route::resource('pilotos', PilotoController::class);
    Route::get('/admin/pilotos/search', [PilotoController::class, 'search'])->name('pilotos.search');

    // Gestión de Circuitos
    Route::resource('circuitos', CircuitoController::class);

    // Gestión de Fechas
    Route::get('fechas/{fecha}/resultados',                               [FechaController::class, 'resultados'])->name('fechas.resultados');
    Route::post('fechas/{fecha}/generar-sesiones',                        [FechaController::class, 'generarSesiones'])->name('fechas.generar-sesiones');
    Route::post('fechas/{fecha}/generar-acumulados',                      [FechaController::class, 'generarAcumulados'])->name('fechas.generar-acumulados');
    Route::delete('fechas/{fecha}/eliminar-sesiones',                     [FechaController::class, 'eliminarSesiones'])->name('fechas.eliminar-sesiones');
    Route::delete('sesiones/{sesion}/eliminar-resultados',                [FechaController::class, 'eliminarResultadosSesion'])->name('fechas.eliminar-resultados-sesion');
    Route::get('fechas/{fecha}/scoring',                                  [FechaController::class, 'scoringFecha'])->name('fechas.scoring');
    Route::post('fechas/{fecha}/scoring/add',                             [FechaController::class, 'addScoringFecha'])->name('fechas.scoring.add');
    Route::patch('fechas/{fecha}/scoring/{sistemaPuntajeFecha}',          [FechaController::class, 'updateScoringFecha'])->name('fechas.scoring.update');
    Route::delete('fechas/{fecha}/scoring/{sistemaPuntajeFecha}/del',     [FechaController::class, 'deleteScoringFecha'])->name('fechas.scoring.delete');
    Route::post('fechas/{fecha}/scoring/reset',                           [FechaController::class, 'resetScoringFecha'])->name('fechas.scoring.reset');
    Route::post('fechas/{fecha}/scoring/customize',                       [FechaController::class, 'customizeScoringFecha'])->name('fechas.scoring.customize');
    Route::resource('fechas', FechaController::class);

    // Gestión de Horarios
    Route::patch('horarios/{horario}/update-from-fecha', [HorarioController::class, 'updateFromFecha'])->name('horarios.update-from-fecha');
    Route::resource('horarios', HorarioController::class);

    // Gestión de Sesiones
    Route::resource('sesiones', SesionDefinicionController::class)->parameters([
        'sesiones' => 'sesion'
    ]);

    // Rutas para búsquedas AJAX
    Route::get('resultados/search-sesiones', [ResultadoSesionController::class, 'searchSesiones'])->name('resultados.search-sesiones');
    Route::get('resultados/search-pilotos', [ResultadoSesionController::class, 'searchPilotos'])->name('resultados.search-pilotos');

    // Gestión de Resultados de Sesiones
    Route::get('resultados/import', [ResultadoSesionController::class, 'importForm'])->name('resultados.import.form');
    Route::post('resultados/import/preview', [ResultadoSesionController::class, 'importPreview'])->name('resultados.import.preview');
    Route::post('resultados/import/store', [ResultadoSesionController::class, 'storeImport'])->name('resultados.import.store');
    
    Route::resource('resultados', ResultadoSesionController::class)->parameters([
        'resultados' => 'resultado'
    ]);


    // Rutas adicionales para funcionalidades específicas
    Route::get('campeonatos/{campeonato}/pilotos', [CampeonatoController::class, 'managePilotos'])->name('campeonatos.pilotos');
    Route::post('campeonatos/{campeonato}/pilotos', [CampeonatoController::class, 'attachPiloto'])->name('campeonatos.pilotos.attach');
    Route::delete('campeonatos/{campeonato}/pilotos/{piloto}', [CampeonatoController::class, 'detachPiloto'])->name('campeonatos.pilotos.detach');
});
