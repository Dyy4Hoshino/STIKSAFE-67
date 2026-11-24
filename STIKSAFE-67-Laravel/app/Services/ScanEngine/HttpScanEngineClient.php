<?php

namespace App\Services\ScanEngine;

use App\Contracts\ScanEngineClient;
use App\Models\Scan;
use App\Models\ScanAttempt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HttpScanEngineClient implements ScanEngineClient
{
    public function scan(Scan $scan, ScanAttempt $attempt): ScanEngineResult
    {
        $baseUrl = rtrim((string) config('services.ml.base_url', 'http://localhost:8001'), '/');
        $token = (string) config('services.ml.service_token', '');
        $timeout = (int) config('services.ml.timeout', 120);
        $requestId = $attempt->ml_request_id ?? (string) Str::uuid();

        if (! Storage::disk($scan->storage_disk)->exists($scan->stored_path)) {
            return new ScanEngineResult(
                status: 'failed',
                errorCode: 'FILE_NOT_FOUND',
                errorMessage: 'File tidak ditemukan di storage karantina.',
            );
        }

        $fileStream = Storage::disk($scan->storage_disk)->readStream($scan->stored_path);

        try {
            $request = Http::withHeaders([
                'X-Request-ID' => $requestId,
                'Idempotency-Key' => $requestId,
            ])
            ->timeout($timeout)
            ->connectTimeout(5);

            if ($token !== '') {
                $request = $request->withToken($token);
            }

            $response = $request
                ->attach('file', $fileStream, $scan->original_filename)
                ->post("{$baseUrl}/v1/scans", [
                    'scan_id' => $scan->id,
                    'request_id' => $requestId,
                    'sha256' => $scan->sha256,
                    'size_bytes' => $scan->file_size,
                    'extension' => $scan->extension,
                    'mime_type' => $scan->mime_type,
                ]);

            if (is_resource($fileStream)) {
                fclose($fileStream);
            }
        } catch (\Throwable $e) {
            if (is_resource($fileStream)) {
                fclose($fileStream);
            }

            return new ScanEngineResult(
                status: 'failed',
                errorCode: 'ML_UNAVAILABLE',
                errorMessage: 'Gagal terhubung ke layanan ML: '.$e->getMessage(),
            );
        }

        if ($response->status() === 429) {
            return new ScanEngineResult(
                status: 'failed',
                errorCode: 'ML_OVERLOADED',
                errorMessage: 'Layanan ML sedang kelebihan beban.',
            );
        }

        if ($response->failed()) {
            $data = $response->json();
            return new ScanEngineResult(
                status: 'failed',
                errorCode: $data['error']['code'] ?? 'ML_INTERNAL_ERROR',
                errorMessage: $data['error']['message'] ?? 'Layanan ML mengembalikan respons error.',
            );
        }

        $data = $response->json();

        // Validasi echo SHA-256 (FR-ML-02)
        if (isset($data['sha256']) && strtolower($data['sha256']) !== strtolower($scan->sha256)) {
            return new ScanEngineResult(
                status: 'failed',
                errorCode: 'ML_INTEGRITY_MISMATCH',
                errorMessage: 'Integritas sha256 respons ML tidak cocok dengan backend.',
            );
        }

        $result = $data['result'] ?? [];

        return new ScanEngineResult(
            status: $data['status'] ?? 'completed',
            label: $result['label'] ?? null,
            confidence: isset($result['confidence']) ? (float) $result['confidence'] : null,
            threatName: $result['threat_name'] ?? null,
            threatCategory: $result['threat_category'] ?? null,
            modelVersion: $data['model_version'] ?? 'unknown',
            errorCode: $data['error']['code'] ?? null,
            errorMessage: $data['error']['message'] ?? null,
        );
    }
}
