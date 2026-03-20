<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fecha;
use App\Models\Imagen;
use Illuminate\Http\JsonResponse;

class ImagenApiController extends Controller
{
    /**
     * GET /api/imagenes/latest
     * Devuelve las imágenes de la última fecha con imágenes disponibles.
     */
    public function latest(): JsonResponse
    {
        $today = now()->toDateString();

        // Obtener la última fecha pasada que tenga imágenes
        $ultimaFecha = Fecha::whereHas('imagenes')
            ->where('fecha_desde', '<=', $today)
            ->orderBy('fecha_desde', 'desc')
            ->first();

        if (!$ultimaFecha) {
            return response()->json([]);
        }

        $imagenes = $ultimaFecha->imagenes()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $formatted = $imagenes->map(fn($img) => [
            'id'          => $img->id,
            'title'       => $img->titulo,
            'description' => $img->descripcion ?? '',
            'image'       => $img->url_cloudinary,
            'date'        => $ultimaFecha->fecha_desde?->translatedFormat('d M Y') ?? '',
        ]);

        // Mezclar aleatoriamente como lo hacía el frontend
        return response()->json($formatted->shuffle()->values());
    }
}
