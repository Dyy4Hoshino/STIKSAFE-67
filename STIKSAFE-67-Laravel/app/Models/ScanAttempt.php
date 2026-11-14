<?php

namespace App\Models;

use App\Enums\ScanAttemptStatus;
use App\Enums\ScanAttemptTrigger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'scan_id',
        'attempt_number',
        'trigger',
        'status',
        'ml_request_id',
        'worker_host',
        'http_status',
        'error_code',
        'error_message',
        'started_at',
        'heartbeat_at',
        'finished_at',
        'duration_ms',
        'triggered_by',
    ];

    protected function casts(): array
    {
        return [
            'trigger' => ScanAttemptTrigger::class,
            'status' => ScanAttemptStatus::class,
            'attempt_number' => 'integer',
            'http_status' => 'integer',
            'duration_ms' => 'integer',
            'started_at' => 'datetime',
            'heartbeat_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}