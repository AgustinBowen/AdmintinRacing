<?php
$p = \App\Models\Piloto::where('nombre', 'LIKE', '%Santiago Villar%')->first();
if(!$p) { echo "No pilot found\n"; exit; }
$res = \App\Models\ResultadoSesion::with('sesion.fecha')->where('piloto_id', $p->id)->get();
echo "Found " . $res->count() . " results for " . $p->nombre . "\n";
foreach($res as $r) {
    echo ($r->sesion->fecha->nombre ?? 'Sin fecha') . ' - ' . $r->sesion->tipo . ': Pts=' . $r->puntos . ', Pos=' . $r->posicion . ', Exc=' . $r->excluido . "\n";
}
