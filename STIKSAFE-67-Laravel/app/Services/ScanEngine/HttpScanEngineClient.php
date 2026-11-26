<?php

namespace App\Services\ScanEngine;

use App\Contracts\ScanEngineClient;
use App\Models\Scan;
use App\Models\ScanAttempt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;

class HttpScanEngineClient implements ScanEngineClient
{
    public function scan(Scan $scan, ScanAttempt $attempt): ScanEngineResult
    {
        $baseUrl = rtrim((string) config('services.ml.base_url', 'http://127.0.0.1:8001'), '/');
        $token = (string) config('services.ml.service_token', '');
        $timeout = (int) config('services.ml.timeout', 120);
        $requestId = $attempt->ml_request_id ?? (string) Str::uuid();

        if (! $scan->stored_path || ! Storage::disk($scan->storage_disk)->exists($scan->stored_path)) {
            return new ScanEngineResult(status: 'failed', errorCode: 'FILE_NOT_FOUND', errorMessage: 'File tidak ditemukan di storage karantina.');
        }

        $fileStream = Storage::disk($scan->storage_disk)->readStream($scan->stored_path);
        if (! is_resource($fileStream)) {
            return new ScanEngineResult(status: 'failed', errorCode: 'FILE_NOT_FOUND', errorMessage: 'File karantina tidak dapat dibaca.');
        }

        try {
            $metadata = json_encode($this->metadata($scan, $requestId), JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $request = Http::withHeaders([
                'X-Request-ID' => $requestId,
                'Idempotency-Key' => $requestId,
            ])->timeout($timeout)->connectTimeout(5);

            if ($token !== '') {
                $request = $request->withToken($token);
            }

            $response = $request
                ->attach('file', $fileStream, $scan->original_filename)
                ->post("{$baseUrl}/v1/scans", ['metadata' => $metadata]);
        } catch (JsonException) {
            return new ScanEngineResult(status: 'failed', errorCode: 'ML_INVALID_REQUEST', errorMessage: 'Metadata permintaan ML tidak dapat dibuat.');
        } catch (\Throwable) {
            return new ScanEngineResult(status: 'failed', errorCode: 'ML_UNAVAILABLE', errorMessage: 'Gagal terhubung ke layanan ML.');
        } finally {
            if (is_resource($fileStream)) {
                fclose($fileStream);
            }
        }

        $data = $response->json();
        if (! is_array($data)) {
            return new ScanEngineResult(status: 'failed', errorCode: 'ML_INVALID_RESPONSE', errorMessage: 'Layanan ML mengembalikan respons non-JSON.');
        }

        if ($response->status() === 429) {
            return $this->failedResult($data, 'ML_OVERLOADED', 'Layanan ML sedang kelebihan beban.');
        }

        if ($response->failed()) {
            return $this->failedResult($data, 'ML_INTERNAL_ERROR', 'Layanan ML mengembalikan respons error.');
        }

        if (strcasecmp((string) ($data['request_id'] ?? ''), $requestId) !== 0
            || strcasecmp((string) ($data['scan_id'] ?? ''), (string) $scan->id) !== 0) {
            return new ScanEngineResult(status: 'failed', errorCode: 'ML_INTEGRITY_MISMATCH', errorMessage: 'Identitas respons ML tidak cocok dengan permintaan.');
        }

        if (! isset($data['sha256']) || ! hash_equals(strtolower($scan->sha256), strtolower((string) $data['sha256']))) {
            return new ScanEngineResult(status: 'failed', errorCode: 'ML_INTEGRITY_MISMATCH', errorMessage: 'Integritas SHA-256 respons ML tidak cocok.');
        }

        $result = $data['result'] ?? null;
        if (! is_array($result) || ! in_array($result['label'] ?? null, ['safe', 'suspicious', 'malicious', 'unknown'], true)) {
            return new ScanEngineResult(status: 'failed', errorCode: 'ML_INVALID_RESPONSE', errorMessage: 'Respons hasil ML tidak valid.');
        }

        $confidence = $result['confidence'] ?? null;
        if ($confidence !== null && (! is_numeric($confidence) || (float) $confidence < 0 || (float) $confidence > 1)) {
            return new ScanEngineResult(status: 'failed', errorCode: 'ML_INVALID_RESPONSE', errorMessage: 'Confidence respons ML tidak valid.');
        }

        return new ScanEngineResult(
            status: (string) ($data['status'] ?? 'completed'),
            label: $result['label'],
            confidence: $confidence === null ? null : (float) $confidence,
            threatName: $result['threat_name'] ?? null,
            threatCategory: $result['threat_category'] ?? null,
            modelVersion: $data['model_version'] ?? null,
            reasonCodes: is_array($result['reason_codes'] ?? null) ? $result['reason_codes'] : [],
            analysis: is_array($data['analysis'] ?? null) ? $data['analysis'] : null,
            preprocessingVersion: $data['preprocessing_version'] ?? null,
            scannedAt: $data['scanned_at'] ?? null,
            durationMs: isset($data['duration_ms']) ? (int) $data['duration_ms'] : null,
        );
    }

    public function scanUrl(string $url, ?string $requestId = null): array
    {
        $requestId ??= (string) Str::uuid();
        $response = $this->request($requestId, 30)->post(rtrim((string) config('services.ml.base_url'), '/').'/v1/urls', [
            'request_id' => $requestId,
            'url' => $url,
        ]);

        return ['http_status' => $response->status(), 'body' => $response->json()];
    }

    public function health(): array
    {
        $requestId = (string) Str::uuid();
        $response = $this->request($requestId, 5)->get(rtrim((string) config('services.ml.base_url'), '/').'/v1/health');

        return ['http_status' => $response->status(), 'body' => $response->json()];
    }

    private function metadata(Scan $scan, string $requestId): array
    {
        $parts = explode('.', basename((string) $scan->original_filename));
        array_shift($parts);
        $extensionChain = array_values(array_slice(array_filter(array_map(
            static fn (string $part): string => strtolower(trim($part)),
            $parts,
        )), 0, 8));

        return [
            'scan_id' => strtoupper((string) $scan->id),
            'request_id' => $requestId,
            'sha256' => strtolower((string) $scan->sha256),
            'size_bytes' => (int) $scan->file_size,
            'submitted_at' => now()->utc()->format('Y-m-d\TH:i:s\Z'),
            'declared_extension' => $scan->extension ? strtolower((string) $scan->extension) : null,
            'extension_chain' => $extensionChain,
            'detected_mime' => $scan->mime_type ?: null,
        ];
    }

    private function request(string $requestId, int $timeout)
    {
        $request = Http::withHeaders(['X-Request-ID' => $requestId])->timeout($timeout)->connectTimeout(5);
        $token = (string) config('services.ml.service_token', '');

        return $token === '' ? $request : $request->withToken($token);
    }

    private function failedResult(array $data, string $fallbackCode, string $fallbackMessage): ScanEngineResult
    {
        return new ScanEngineResult(
            status: 'failed',
            errorCode: $data['error']['code'] ?? $fallbackCode,
            errorMessage: $data['error']['message'] ?? $fallbackMessage,
            modelVersion: $data['model_version'] ?? null,
        );
    }
}
