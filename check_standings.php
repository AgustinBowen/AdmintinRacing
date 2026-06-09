<?php
$piloto = \App\Models\Piloto::where('nombre', 'LIKE', '%Santiago Villar%')->first();
if (!$piloto) { echo 'No encontrado'; exit; }
$campeonato = \App\Models\Campeonato::first(); // Assuming there's only one or we grab the first
$standings = (new \App\Services\StandingsService())->calcular($campeonato);
if (isset($standings[$piloto->id])) {
    echo "Total: " . $standings[$piloto->id]['total'] . "\n";
    print_r($standings[$piloto->id]['fechas']);
} else {
    echo "No standings\n";
}
