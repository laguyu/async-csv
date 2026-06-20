<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CsvImport extends Model
{
    // Habilitamos los campos para controlar el estado de la importación
    protected $fillable = [
        'filename',
        'status',
        'total_rows',
        'processed_rows',
    ];
}
