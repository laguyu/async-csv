<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportCsvRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'csv_file' => 'required|file|mimes:csv,txt|max:20480', // Obligatorio, formato plano, máx 20MB
        ];
    }
}

