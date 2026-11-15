<?php

namespace App\Services\ScanEngine;

class ScanEngineResult
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $label = null,
        public readonly ?float $confidence = null,
        public readonly ?string $threatName = null,
        public readonly ?string $threatCategory = null,
        public readonly ?string $modelVersion = null,
        public readonly ?string $errorCode = null,
        public readonly ?string $errorMessage = null,
    ) {}

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
