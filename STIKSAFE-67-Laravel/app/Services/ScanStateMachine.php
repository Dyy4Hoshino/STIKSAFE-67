<?php

namespace App\Services;

use App\Enums\ScanStatus;
use App\Models\Scan;
use Illuminate\Support\Facades\DB;
use LogicException;

class ScanStateMachine
{
    private const TRANSITIONS = [
        'queued' => ['processing', 'cancelled', 'failed', 'rejected'],
        'processing' => ['completed', 'queued', 'failed', 'cancelled'],
        'failed' => ['queued'],
    ];

    public function transition(Scan $scan, ScanStatus $to, array $attributes = []): Scan
    {
        return DB::transaction(function () use ($scan, $to, $attributes): Scan {
            $lockedScan = Scan::query()
                ->whereKey($scan->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $from = $lockedScan->status;

            if ($from === $to) {
                return $lockedScan;
            }

            if (! $this->canTransition($from, $to)) {
                throw new LogicException("Invalid scan transition: {$from->value} -> {$to->value}");
            }

            if ($from === ScanStatus::Completed) {
                throw new LogicException('Completed scan is immutable.');
            }

            $lockedScan->forceFill([
                'status' => $to,
                ...$attributes,
            ])->save();

            return $lockedScan->refresh();
        });
    }

    public function canTransition(ScanStatus $from, ScanStatus $to): bool
    {
        return in_array($to->value, self::TRANSITIONS[$from->value] ?? [], true);
    }
}
