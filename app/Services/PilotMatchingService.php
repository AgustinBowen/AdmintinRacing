<?php

namespace App\Services;

use App\Models\Piloto;

class PilotMatchingService
{
    /**
     * Identifies pilots from OCR scan data by matching names against the database.
     */
    public function matchFromOcrData(array $resultadosJson): array
    {
        $pilotos = Piloto::orderBy('nombre')->get();
        
        $nombresPilotos = $pilotos->pluck('id', 'nombre')->mapWithKeys(function ($item, $key) {
            return [strtolower(trim($key)) => $item];
        })->toArray();

        foreach ($resultadosJson as &$row) {
            $nombreScan = strtolower(trim($row['nombre'] ?? ''));
            if (empty($nombreScan)) {
                $row['piloto_id_match'] = null;
                continue;
            }

            // 1. Exact match
            if (isset($nombresPilotos[$nombreScan])) {
                $row['piloto_id_match'] = $nombresPilotos[$nombreScan];
            } else {
                // 2. Strict intersection match by parts
                $row['piloto_id_match'] = null;
                $parts = array_filter(explode(' ', $nombreScan), fn($p) => strlen($p) > 2);
                
                if (count($parts) > 0) {
                    foreach ($nombresPilotos as $dbName => $id) {
                        $allPartsExist = true;
                        foreach ($parts as $p) {
                            if (!str_contains($dbName, $p)) {
                                $allPartsExist = false;
                                break;
                            }
                        }

                        if ($allPartsExist) {
                            $row['piloto_id_match'] = $id;
                            break; 
                        }
                    }
                }
            }
        }

        return $resultadosJson;
    }
}
