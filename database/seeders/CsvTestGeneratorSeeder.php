<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CsvTestGeneratorSeeder extends Seeder
{
    public function run(): void
    {
        $filename = 'big_products_test.csv';

        // Abre un puntero de archivo en el almacenamiento local de Laravel
        $filePath = Storage::path($filename);
        $handle = fopen($filePath, 'w');

        // 1. Escribe las cabeceras exactas que espera el servicio
        fputcsv($handle, ['sku', 'name', 'description', 'price', 'stock']);

        // 2. Genera 20,000 registros ficticios sin saturar la memoria
        $totalRecords = 20000;

        for ($i = 1; $i <= $totalRecords; $i++) {
            $sku = 'PROD-' . str_pad($i, 6, '0', STR_PAD_LEFT); // Genera PROD-000001, etc.
            $name = 'Producto Ficticio de Prueba #' . $i;
            $description = 'Esta es la descripcion detallada del producto numero ' . $i . ' generada para pruebas de estres.';
            $price = number_format(rand(10, 1500) + (rand(0, 99) / 100), 2, '.', ''); // Precios entre 10.00 y 1500.99
            $stock = rand(5, 500);

            fputcsv($handle, [$sku, $name, $description, $price, $stock]);
        }

        fclose($handle);

        $this->command->info("¡Exito! Se ha creado el archivo CSV con {$totalRecords} productos en: " . Storage::path($filename));
    }
}

