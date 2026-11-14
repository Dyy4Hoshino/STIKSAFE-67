<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidScanFile implements ValidationRule
{
    private const ALLOWED_TYPES = [
        'pdf' => ['application/pdf'],
        'txt' => ['text/plain'],
        'png' => ['image/png'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('File gagal diunggah.');
            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());
        $mime = $value->getMimeType();

        if (! isset(self::ALLOWED_TYPES[$extension])) {
            $fail('Tipe file tidak diizinkan.');
            return;
        }

        if (! in_array($mime, self::ALLOWED_TYPES[$extension], true)) {
            $fail('Isi file tidak cocok dengan ekstensi.');
        }
    }
}
