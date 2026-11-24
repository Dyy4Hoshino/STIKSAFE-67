<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    private const ALLOWED = [
        'scan.max_file_size_mb' => ['integer', 1, 100],
        'scan.max_total_duration_min' => ['integer', 1, 60],
        'retention.file_days' => ['integer', 1, 365],
        'ml.timeout_seconds' => ['integer', 1, 120],
    ];

    public function index(): View
    {
        $settings = SystemSetting::query()->orderBy('setting_key')->get()->keyBy('setting_key');
        $mlStatus = Cache::remember('admin.ml_service_status', 10, function (): array {
            $url = rtrim((string) (config('services.ml.base_url') ?: config('services.ml.url')), '/');
            if ($url === '') {
                return ['available' => false, 'message' => 'URL ML belum dikonfigurasi.'];
            }

            try {
                $response = Http::timeout(3)->get($url.'/health');
                return ['available' => $response->successful(), 'message' => $response->successful() ? 'Online' : 'Health check gagal.'];
            } catch (\Throwable) {
                return ['available' => false, 'message' => 'ML service tidak dapat dijangkau.'];
            }
        });

        return view('admin.settings.index', compact('settings', 'mlStatus'));
    }

    public function update(Request $request, AuditLogService $audit)
    {
        $values = $request->input('settings', []);
        if (! is_array($values)) {
            throw ValidationException::withMessages(['settings' => 'Format pengaturan tidak valid.']);
        }

        foreach ($values as $key => $value) {
            if (! isset(self::ALLOWED[$key])) {
                throw ValidationException::withMessages(["settings.{$key}" => 'Pengaturan tidak diizinkan.']);
            }
            [$type, $min, $max] = self::ALLOWED[$key];
            if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < $min || (int) $value > $max) {
                throw ValidationException::withMessages(["settings.{$key}" => "Nilai harus integer antara {$min} dan {$max}."]);
            }
        }

        foreach ($values as $key => $value) {
            $setting = SystemSetting::query()->firstOrNew(['setting_key' => $key]);
            $setting->forceFill([
                'setting_value' => (int) $value,
                'value_type' => self::ALLOWED[$key][0],
                'is_public' => false,
                'updated_by' => $request->user()->id,
            ])->save();
        }

        Cache::forget('admin.ml_service_status');
        $audit->record('settings.updated', 'system_settings', null, ['keys' => array_keys($values)], severity: 'warn', request: $request);

        return back()->with('success', 'Pengaturan sistem disimpan.');
    }
}
