<?php

namespace App\Enums;

enum ScanResultLabel: string
{
    case Safe = 'safe';
    case Suspicious = 'suspicious';
    case Malicious = 'malicious';
    case Unknown = 'unknown';
}