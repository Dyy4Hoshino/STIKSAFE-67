<?php

namespace Tests\Unit;

use App\Enums\ScanAttemptStatus;
use App\Enums\ScanStatus;
use App\Models\Scan;
use App\Models\ScanAttempt;
use App\Services\ScanEngine\HttpScanEngineClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HttpScanEngineClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_sends_metadata_json_and_file_per_contract_v12(): void
    {
        Storage::fake('private');
        $stored = Storage::disk('private')->put('quarantine/test.exe', 'binary content');

        $scan = Scan::factory()->create([
            'original_filename' => 'laporan.pdf.exe',
            'extension' => 'exe',
            'mime_type' => 'application/x-dosexec',
            'file_size' => 14,
            'sha256' => hash('sha256', 'binary content'),
            'stored_path' => 'quarantine/test.exe',
            'status' => ScanStatus::Processing,
        ]);

        $attempt = $scan->attempts()->create([
            'attempt_number' => 1,
            'trigger' => \App\Enums\ScanAttemptTrigger::Initial,
            'status' => ScanAttemptStatus::Running,
            'ml_request_id' => '7f0c1d5e-4b7a-4a39-9c62-0e5a3d1b2f10',
        ]);

        Http::fake([
            'http://127.0.0.1:8001/v1/scans' => Http::response([
                'request_id' => '7f0c1d5e-4b7a-4a39-9c62-0e5a3d1b2f10',
                'scan_id' => $scan->id,
                'status' => 'completed',
                'sha256' => $scan->sha256,
                'result' => [
                    'label' => 'suspicious',
                    'confidence' => 0.885,
                    'threat_name' => null,
                    'threat_category' => null,
                    'reason_codes' => ['DOUBLE_EXTENSION'],
                ],
                'model_version' => 'ember2024-pe-baseline-1',
                'preprocessing_version' => 'thrember-ember-feature-v3',
                'analysis' => ['method' => 'model', 'family' => 'pe'],
                'scanned_at' => '2026-11-24T04:00:05Z',
                'duration_ms' => 1200,
            ], 200),
        ]);

        config(['services.ml.base_url' => 'http://127.0.0.1:8001']);
        config(['services.ml.service_token' => 'token-lokal-123']);

        $client = new HttpScanEngineClient();
        $result = $client->scan($scan, $attempt);

        $this->assertTrue($result->isCompleted());
        $this->assertSame('suspicious', $result->label);
        $this->assertSame(['DOUBLE_EXTENSION'], $result->reasonCodes);
        $this->assertSame('model', $result->analysis['method']);

        Http::assertSent(function ($request) {
            $hasAuth = $request->hasHeader('Authorization', 'Bearer token-lokal-123');
            $hasRequestId = $request->hasHeader('X-Request-ID', '7f0c1d5e-4b7a-4a39-9c62-0e5a3d1b2f10');
            $metadata = collect($request->data())->firstWhere('name', 'metadata')['contents'] ?? null;
            $hasChain = $metadata && str_contains((string) $metadata, '"extension_chain":["pdf","exe"]');

            return $hasAuth && $hasRequestId && $hasChain;
        });
    }
}
