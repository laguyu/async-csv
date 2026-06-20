<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\ImportStatusController;

Route::post('/products/import', [ProductImportController::class, 'import']);
Route::get('/products/import/{id}', [ImportStatusController::class, 'show']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
