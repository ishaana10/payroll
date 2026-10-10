<?php

namespace Nuvis\FijiPayroll\Enums;

enum PayrollStatus: string
{
    case Draft = 'draft';
    case Processing = 'processing';
    case Calculated = 'calculated';
    case Approved = 'approved';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Processing => 'Processing',
            self::Calculated => 'Calculated',
            self::Approved => 'Approved',
            self::Paid => 'Paid',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Processing => 'warning',
            self::Calculated => 'info',
            self::Approved => 'success',
            self::Paid => 'success',
            self::Cancelled => 'danger',
        };
    }
}
