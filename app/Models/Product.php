<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // Habilitamos los campos para que se puedan insertar masivamente desde el CSV
    protected $fillable = [
        'sku',
        'name',
        'description',
        'price',
        'stock',
    ];
}
