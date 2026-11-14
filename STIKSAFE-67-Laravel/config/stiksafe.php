<?php

return [
    'retention' => [
        'file_days' => (int) env('STIKSAFE_FILE_RETENTION_DAYS', 7),
    ],
];
