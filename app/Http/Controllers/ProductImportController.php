<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportCsvRequest;
use App\Models\CsvImport;
use App\Jobs\ProcessProductCsv;

class ProductImportController extends Controller
{
        public function import(ImportCsvRequest $request)
    {
        $file = $request->file('csv_file');

        //  Abre únicamente la primera línea del archivo para validar las columnas en milisegundos
        if (($handle = fopen($file->getRealPath(), 'r')) !== FALSE) {
            $headers = fgetcsv($handle, 1000, ',');
            fclose($handle);

            // Campos obligatorios requeridos por el negocio y el modelo de datos
            $requiredHeaders = ['sku', 'name', 'description', 'price', 'stock'];

            // Comprueba que todas las columnas requeridas existan dentro de la cabecera del archivo
            if (!$headers || count(array_intersect($requiredHeaders, $headers)) !== count($requiredHeaders)) {
                return response()->json([
                    'message' => 'Los datos proporcionados no son válidos.',
                    'errors' => [
                        'csv_file' => ['El archivo CSV no contiene la estructura de columnas obligatoria: sku, name, description, price, stock.']
                    ]
                ], 422); // Código 422: Error de validación de datos (Unprocessable Entity)
            }
        }

        // 1. Guarda el archivo en el storage local de forma rápida (Solo si la cabecera es válida)
        $path = $file->store('imports');

        // 2. Crea el registro de auditoría/estado
        $import = CsvImport::create([
            'filename' => $file->getClientOriginalName(),
            'status' => 'pending'
        ]);

        // 3. Despacha a la cola de manera asíncrona
        ProcessProductCsv::dispatch($path, $import->id);

        // 4. Responde inmediatamente con Código 202 (Accepted)
        return response()->json([
            'message' => 'El archivo fue recibido con éxito y se está procesando en segundo plano.',
            'import_id' => $import->id
        ], 202);
    }

}
