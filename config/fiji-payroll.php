<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Employee Model
    |--------------------------------------------------------------------------
    | Used by the Process Payroll multi-select. Point this at your Employees
    | model (must be an Eloquent model).
    */
    'employee_model' => env('FIJI_EMPLOYEE_MODEL', \App\Models\Employee::class),


    /*
    |--------------------------------------------------------------------------
    | Employer Identifiers (for exports)
    |--------------------------------------------------------------------------
    */
    "employer_tin" => env("FIJI_EMPLOYER_TIN", ""),
    "employer_fnpf_reference" => env("FIJI_EMPLOYER_FNPF_REF", ""),


    /*
    |--------------------------------------------------------------------------
    | FNPF Contribution Rates (as of 1 August 2026 – 31 July 2027)
    |--------------------------------------------------------------------------
    | Temporary reduction of employer rate from 10% to 8%.
    | Employee remains 8%. Combined 16%.
    | Update these values when rates change.
    */
    'fnpf' => [
        'employee_rate' => 0.08,   // 8%
        'employer_rate' => 0.08,   // 8% (temporary until 31 Jul 2027)
        'include_overtime' => true,
        'include_allowances' => true, // most ordinary allowances are included
    ],

    /*
    |--------------------------------------------------------------------------
    | PAYE Tax Brackets (Resident Individuals – simplified progressive)
    |--------------------------------------------------------------------------
    | Based on current FRCS structure.
    | Amounts are annual. Calculator converts period amounts to annual equivalent.
    */
    'paye' => [
        'tax_free_threshold' => 30000, // FJD

        'brackets' => [
            // [min annual, max annual, rate, fixed amount for this band]
            ['min' => 0,       'max' => 30000,  'rate' => 0.00, 'fixed' => 0],
            ['min' => 30001,   'max' => 50000,  'rate' => 0.18, 'fixed' => 0],
            ['min' => 50001,   'max' => 270000, 'rate' => 0.20, 'fixed' => 3600],
            // Higher bands incorporate SRT – extend as needed
            ['min' => 270001,  'max' => null,   'rate' => 0.20, 'fixed' => 47600], // base + SRT handling recommended separately
        ],

        // Social Responsibility Tax & ECAL thresholds (simplified – expand for full accuracy)
        'srt_threshold' => 270000,
        'srt_rate' => 0.13, // starting rate, progressive higher
    ],

    /*
    |--------------------------------------------------------------------------
    | Other Levies (optional / configurable)
    |--------------------------------------------------------------------------
    */
    'levies' => [
        'workcare_rate' => 0.01,          // ~1% accident compensation (confirm applicability)
        'training_levy_rate' => 0.01,     // Fiji National Training / University levy
        'enable_workcare' => true,
        'enable_training_levy' => false,  // enable per company setting
    ],

    /*
    |--------------------------------------------------------------------------
    | Payroll Settings
    |--------------------------------------------------------------------------
    */
    'currency' => 'FJD',
    'default_pay_frequency' => 'monthly', // monthly | fortnightly | weekly
    'payslip_template' => 'fiji-payroll::payslip',

    /*
    |--------------------------------------------------------------------------
    | Reporting Deadlines (informational)
    |--------------------------------------------------------------------------
    */
    'deadlines' => [
        'fnpf_schedule' => 14,        // day of following month
        'paye_remittance' => 'last_day', // last day of following month
    ],
];
