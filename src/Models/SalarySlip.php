<?php

namespace Nuvis\FijiPayroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalarySlip extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'employee_name',
        'employee_number',
        'tin',
        'fnpf_number',
        'tax_code',
        'is_resident',
        'basic',
        'overtime',
        'allowances',
        'other_earnings',
        'gross',
        'fnpf_base',
        'employee_fnpf',
        'employer_fnpf',
        'taxable_income',
        'paye',
        'other_deductions',
        'net_pay',
        'workcare_levy',
        'training_levy',
        'employer_cost',
        'employee_fnpf_rate',
        'employer_fnpf_rate',
        'calculation_details',
        'status',
    ];

    protected $casts = [
        'is_resident' => 'boolean',
        'basic' => 'decimal:2',
        'overtime' => 'decimal:2',
        'allowances' => 'decimal:2',
        'other_earnings' => 'decimal:2',
        'gross' => 'decimal:2',
        'fnpf_base' => 'decimal:2',
        'employee_fnpf' => 'decimal:2',
        'employer_fnpf' => 'decimal:2',
        'taxable_income' => 'decimal:2',
        'paye' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'workcare_levy' => 'decimal:2',
        'training_levy' => 'decimal:2',
        'employer_cost' => 'decimal:2',
        'employee_fnpf_rate' => 'decimal:4',
        'employer_fnpf_rate' => 'decimal:4',
        'calculation_details' => 'array',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    // Assumes an Employee model exists in the main ERP
    public function employee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Employee::class); // adjust namespace if needed
    }
}
