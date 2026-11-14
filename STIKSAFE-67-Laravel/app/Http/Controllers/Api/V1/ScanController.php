<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScanRequest;
use App\Http\Resources\ScanResource;
use App\Services\CreateScanService;
use Illuminate\Http\JsonResponse;

class ScanController extends Controller
{
    public function store(StoreScanRequest $request, CreateScanService $createScan): JsonResponse
    {
        $scan = $createScan->create(
            $request->user()->getKey(),
            $request->file('file'),
        );

        return (new ScanResource($scan))
            ->additional([
                'success' => true,
                'message' => 'File diterima dan masuk antrean pemindaian.',
                'meta' => [
                    'request_id' => $request->header('X-Request-ID'),
                ],
            ])
            ->response()
            ->setStatusCode(202);
    }
}
