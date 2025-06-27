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
            'campeonatos' => Campeonato::count(),
            'pilotos' => Piloto::count(),
            'circuitos' => Circuito::count(),
            'fechas' => Fecha::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
