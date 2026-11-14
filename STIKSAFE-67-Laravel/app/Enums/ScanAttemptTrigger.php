<?php

namespace App\Enums;

enum ScanAttemptTrigger: string
{
    case Initial = 'initial';
    case AutoRetry = 'auto_retry';
    case ManualRetry = 'manual_retry';
}