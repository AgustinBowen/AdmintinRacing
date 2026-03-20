<?php

use App\Http\Controllers\Api\CampeonatoApiController;
use App\Http\Controllers\Api\FechaApiController;
use App\Http\Controllers\Api\ImagenApiController;
use App\Http\Controllers\Api\StatsApiController;
use Illuminate\Support\Facades\Route;

// Middleware CORS para todas las rutas API
Route::middleware('api')->group(function () {

    // -------------------------------------------------------
    // Campeonatos
    // -------------------------------------------------------
    Route::get('/campeonatos',              [CampeonatoApiController::class, 'index']);
    Route::get('/campeonatos/current',      [CampeonatoApiController::class, 'current']);
    Route::get('/campeonatos/{id}/standings', [CampeonatoApiController::class, 'standings']);
    Route::get('/campeonatos/{id}/fechas',  [CampeonatoApiController::class, 'fechas']);

    // -------------------------------------------------------
    // Fechas / Carreras
    // -------------------------------------------------------
    Route::get('/fechas',         [FechaApiController::class, 'index']);
    Route::get('/fechas/next',    [FechaApiController::class, 'next']);
    Route::get('/fechas/latest',  [FechaApiController::class, 'latest']);
    Route::get('/fechas/{id}',    [FechaApiController::class, 'show']);

    // -------------------------------------------------------
    // Estadísticas
    // -------------------------------------------------------
    Route::get('/stats', [StatsApiController::class, 'index']);

    // -------------------------------------------------------
    // Imágenes
    // -------------------------------------------------------
    Route::get('/imagenes/latest', [ImagenApiController::class, 'latest']);
});
