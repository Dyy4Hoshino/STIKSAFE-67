<?php

namespace App\Services\ScanEngine;

use App\Contracts\ScanEngineClient;
use App\Models\Scan;
use App\Models\ScanAttempt;

class FakeScanEngineClient implements ScanEngineClient
{
    public function scan(Scan $scan, ScanAttempt $attempt): ScanEngineResult
    {
        usleep(500_000);

        $scenarios = [
            'safe' => new ScanEngineResult(
                status: 'completed',
                label: 'safe',
                confidence: 0.9812,
                modelVersion: 'stiksafe-ml-mock-1.0',
            ),
            'suspicious' => new ScanEngineResult(
                status: 'completed',
                label: 'suspicious',
                confidence: 0.7200,
                modelVersion: 'stiksafe-ml-mock-1.0',
            ),
            'malicious' => new ScanEngineResult(
                status: 'completed',
                label: 'malicious',
                confidence: 0.9650,
                threatName: 'Trojan.GenericML',
                threatCategory: 'trojan',
                modelVersion: 'stiksafe-ml-mock-1.0',
            ),
            'unknown' => new ScanEngineResult(
                status: 'completed',
                label: 'unknown',
                confidence: 0.3100,
                modelVersion: 'stiksafe-ml-mock-1.0',
            ),
            'error' => new ScanEngineResult(
                status: 'failed',
                errorCode: 'ML_INTERNAL_ERROR',
                errorMessage: 'Simulasi error dari Fake ML.',
            ),
        ];

        $key = match (true) {
            str_contains($scan->original_filename, 'malware') => 'malicious',
            str_contains($scan->original_filename, 'suspicious') => 'suspicious',
            str_contains($scan->original_filename, 'unknown') => 'unknown',
            str_contains($scan->original_filename, 'error') => 'error',
            default => 'safe',
        };

        return $scenarios[$key];
    }
}
