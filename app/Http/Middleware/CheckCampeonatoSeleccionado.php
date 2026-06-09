<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCampeonatoSeleccionado
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('campeonato_id')) {
            return redirect()->route('admin.categorias.index')
                ->with('warning', 'Debes seleccionar una categoría y campeonato para acceder.');
        }

        return $next($request);
    }
}

