<?php

namespace App\Http\Requests;

use App\Rules\ValidScanFile;
use Illuminate\Foundation\Http\FormRequest;

class StoreScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'bail',
                'required',
                'file',
                'max:25600',
                new ValidScanFile,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File wajib diunggah.',
            'file.file' => 'Upload file tidak valid.',
            'file.max' => 'Ukuran file maksimum 25 MB.',
        ];
    }
}