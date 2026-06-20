<?php
namespace App\Services;

use App\Contracts\DataProcessorInterface;
use App\Models\Product;
use App\Models\CsvImport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CsvProductProcessor implements DataProcessorInterface
{
    public function process(string $filePath, int $importId): void
    {
        $import = CsvImport::findOrFail($importId);
        $import->update(['status' => 'processing']);

        $fullPath = Storage::path($filePath);

        // fgetcsv lee línea por línea sin cargar el archivo entero en memoria
        if (!file_exists($fullPath) || !($handle = fopen($fullPath, 'r'))) {
            $import->update(['status' => 'failed']);
            return;
        }

        // Extraer las cabeceras (ej: sku,name,description,price,stock)
        $headers = fgetcsv($handle, 1000, ',');
        $rowCount = 0;
        $chunk = [];

        while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $rowCount++;

            // Combina las cabeceras con los valores de la fila actual
            $data = array_combine($headers, $row);

            // Validar la consistencia de los datos internos
            if ($this->validateRow($data)->fails()) {
                continue; // Si falla, ignora la fila
            }

            // Estructurar el registro para inserción masiva
            $chunk[] = [
                'sku'         => $data['sku'],
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'price'       => $data['price'],
                'stock'       => $data['stock'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            // Cuando el bloque llega a 500 registros, se guardan juntos en una sola query
            if (count($chunk) >= 500) {
                $this->persistChunk($chunk);
                $chunk = []; // Liberamos la memoria del array
                $import->increment('processed_rows', 500);
            }
        }

        // Insertar los registros sobrantes que no alcanzaron los 500 exactos
        if (count($chunk) > 0) {
            $this->persistChunk($chunk);
            $import->increment('processed_rows', count($chunk));
        }

        fclose($handle);

        $import->update([
            'status' => 'completed',
            'total_rows' => $rowCount
        ]);

        // Limpiar el archivo temporal una vez terminado
        Storage::delete($filePath);
    }

    private function validateRow(array $data)
    {
        return Validator::make($data, [
            'sku'         => 'required|string|max:50',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
        ]);
    }

    private function persistChunk(array $chunk): void
    {
        //'upsert' inserta los nuevos registros, pero si el 'sku' ya existe, actualiza los campos indicados de golpe.
        Product::upsert($chunk, ['sku'], ['name', 'description', 'price', 'stock']);
    }
}
