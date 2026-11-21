<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidScanFile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('File gagal diunggah.');
            return;
        }

        // Cek ukuran file tidak kosong
        if ($value->getSize() <= 0) {
            $fail('File kosong tidak dapat dipindai.');
            return;
        }

        // Semua tipe file diizinkan masuk ke sistem pemindaian
        // Termasuk file yang ekstensinya tidak sesuai dengan isinya (penyamaran file/disguised file),
        // karena sistem Machine Learning yang akan menganalisis isi biner aslinya.
    }
}
