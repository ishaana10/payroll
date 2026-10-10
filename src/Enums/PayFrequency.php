<?php

namespace Nuvis\FijiPayroll\Enums;

enum PayFrequency: string
{
    case Monthly = 'monthly';
    case Fortnightly = 'fortnightly';
    case Weekly = 'weekly';

    public function periodsPerYear(): int
    {
        return match ($this) {
            self::Monthly => 12,
            self::Fortnightly => 26,
            self::Weekly => 52,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Monthly',
            self::Fortnightly => 'Fortnightly',
            self::Weekly => 'Weekly',
        };
    }
}
