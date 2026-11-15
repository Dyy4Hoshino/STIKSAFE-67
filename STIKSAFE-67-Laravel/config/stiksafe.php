<?php

return [
    'retention' => [
        'file_days' => (int) env('STIKSAFE_FILE_RETENTION_DAYS', 7),
    ],

    'scan' => [
        'max_total_duration_min' => (int) env('STIKSAFE_SCAN_MAX_DURATION_MIN', 15),
    ],
];
