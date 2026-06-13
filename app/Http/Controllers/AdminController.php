<?php
// app/Http/Controllers/AdminController.php
namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Piloto;
use App\Models\Circuito;
use App\Models\Fecha;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'campeonatos' => \App\Models\Campeonato::where('categoria_id', session('categoria_id'))->count(),
            'pilotos' => \App\Models\Piloto::count(),
            'circuitos' => \App\Models\Circuito::count(),
            'fechas' => \App\Models\Fecha::count(),
        ];

        $top5 = [];
        $campeonatoId = session('campeonato_id');
        if ($campeonatoId) {
            $top5 = \App\Models\PosicionCampeonato::with('piloto')
                ->where('campeonato_id', $campeonatoId)
                ->orderByDesc('puntos_totales')
                ->take(5)
                ->get();
        }

        return view('admin.dashboard', compact('stats', 'top5'));
    }
}
