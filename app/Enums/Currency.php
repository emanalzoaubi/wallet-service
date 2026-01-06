<?php

namespace App\Enums;

enum Currency: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case JPY = 'JPY';
    case CAD = 'CAD';
    case AUD = 'AUD';
    case CHF = 'CHF';
    case CNY = 'CNY';
    case INR = 'INR';
    case BRL = 'BRL';
    case SYP = 'SYP';
    case SAR = 'SAR';
    case AED = 'AED';
    case KWD = 'KWD';
    case QAR = 'QAR';
    case BHD = 'BHD';
    case OMR = 'OMR';
    case KRW = 'KRW';
    case IDR = 'IDR';

    /**
     * Get all currency codes as an array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

