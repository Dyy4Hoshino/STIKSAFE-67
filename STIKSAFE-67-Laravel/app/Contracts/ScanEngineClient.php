<?php

namespace App\Contracts;

use App\Models\Scan;
use App\Models\ScanAttempt;
use App\Services\ScanEngine\ScanEngineResult;

interface ScanEngineClient
{
    public function scan(Scan $scan, ScanAttempt $attempt): ScanEngineResult;
}
