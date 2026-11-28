<?php

namespace App\Models;

use App\Enums\ScanResultLabel;
use App\Enums\ScanStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Database\Factories\ScanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Scan extends Model
{
    use HasFactory, HasUlids;

    protected static function newFactory()
    {
        return ScanFactory::new();
    }

    protected $fillable = [
        'user_id',
        'original_filename',
        'extension',
        'mime_type',
        'file_size',
        'sha256',
        'storage_disk',
        'stored_path',
        'status',
        'result_label',
        'confidence',
        'threat_name',
        'threat_category',
        'model_version',
        'reason_codes',
        'analysis',
        'preprocessing_version',
        'ml_request_id',
        'error_code',
        'error_message',
        'attempts_count',
        'cancel_requested_at',
        'queued_at',
        'started_at',
        'completed_at',
        'file_purged_at',
        'file_expires_at',
        'user_deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ScanStatus::class,
            'result_label' => ScanResultLabel::class,
            'confidence' => 'decimal:4',
            'reason_codes' => 'array',
            'analysis' => 'array',
            'file_size' => 'integer',
            'attempts_count' => 'integer',
            'cancel_requested_at' => 'datetime',
            'queued_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'file_purged_at' => 'datetime',
            'file_expires_at' => 'datetime',
            'user_deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(ScanFeedback::class);
    }


    public function attempts(): HasMany
    {
        return $this->hasMany(ScanAttempt::class);
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }
}