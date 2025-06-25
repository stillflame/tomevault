<?php

namespace App\Enums;

enum DangerLevel: string
{
    case Low = 'Low';
    case Medium = 'Medium';
    case High = 'High';
    case Severe = 'Severe';
    case Unknown = 'Unknown';
}
