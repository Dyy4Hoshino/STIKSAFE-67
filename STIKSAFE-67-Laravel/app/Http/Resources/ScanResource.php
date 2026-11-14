<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->status?->value;

        return [
            'id' => $this->id,
            'filename' => $this->original_filename,
            'file' => [
                'size_bytes' => $this->file_size,
                'extension' => $this->extension,
                'mime_type' => $this->mime_type,
                'available' => $this->stored_path !== null && $this->file_purged_at === null,
            ],
            'status' => $status,
            'result' => $status === 'completed' ? [
                'label' => $this->result_label?->value,
                'confidence' => $this->confidence !== null ? (float) $this->confidence : null,
                'threat_name' => $this->threat_name,
                'threat_category' => $this->threat_category,
                'model_version' => $this->model_version,
                'disclaimer' => 'Hasil merupakan prediksi model dan bukan jaminan mutlak.',
            ] : null,
            'error' => $status === 'failed' ? [
                'code' => $this->error_code,
                'message' => $this->error_message,
            ] : null,
            'timestamps' => [
                'uploaded_at' => $this->created_at?->toISOString(),
                'queued_at' => $this->queued_at?->toISOString(),
                'started_at' => $this->started_at?->toISOString(),
                'completed_at' => $this->completed_at?->toISOString(),
            ],
            'attempts_count' => $this->attempts_count,
        ];
    }
}
