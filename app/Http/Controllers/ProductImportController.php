<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportCsvRequest;
use App\Models\CsvImport;
use App\Jobs\ProcessProductCsv;

class ProductImportController extends Controller
{
    public function import(ImportCsvRequest $request)
    {
        // 1. Guardar el archivo en el storage local de forma rápida
        $path = $request->file('csv_file')->store('imports');

        // 2. Crear el registro de auditoría/estado
        $import = CsvImport::create([
            'filename' => $request->file('csv_file')->getClientOriginalName(),
            'status' => 'pending'
        ]);

        // 3. Despachar a la cola de manera asíncrona
        ProcessProductCsv::dispatch($path, $import->id);

        // 4. Responder inmediatamente con Código 202 (Accepted)
        return response()->json([
            'message' => 'El archivo fue recibido con éxito y se está procesando en segundo plano.',
            'import_id' => $import->id
        ], 202);
    }
}
