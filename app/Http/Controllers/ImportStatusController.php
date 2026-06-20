<?php

namespace App\Http\Controllers;

use App\Models\CsvImport;
use Illuminate\Http\JsonResponse;

class ImportStatusController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/products/import/{id}",
     *      summary="Consultar el estado del progreso de la importación",
     *      tags={"Importación"},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la importación generado al subir el CSV",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Estado del proceso actual."
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Importación no encontrada."
     *      )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $import = CsvImport::findOrFail($id);

        // Calcular porcentaje matemático del avance real
        $progress = 0;
        if ($import->total_rows > 0) {
            $progress = round(($import->processed_rows / $import->total_rows) * 100, 2);
        }

        return response()->json([
            'id' => $import->id,
            'filename' => $import->filename,
            'status' => $import->status, // pending, processing, completed, failed
            'total_rows' => $import->total_rows,
            'processed_rows' => $import->processed_rows,
            'progress' => $progress . '%'
        ], 200);
    }
}
