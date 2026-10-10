<?php

namespace Nuvis\FijiPayroll\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Nuvis\FijiPayroll\Enums\PayFrequency;
use Nuvis\FijiPayroll\Enums\PayrollStatus;
use Nuvis\FijiPayroll\Models\PayrollRun;
use Nuvis\FijiPayroll\Models\SalarySlip;

/**
 * Full Payroll Processing Service
 *
 * Accepts a PayrollRun + collection of employees (or employee data arrays),
 * runs calculations, and creates SalarySlip records in a transaction.
 */
class PayrollProcessor
{
    public function __construct(
        protected PayrollCalculator $calculator
    ) {}

    /**
     * Process payroll for the given employees.
     *
     * @param  PayrollRun  $payrollRun
     * @param  Collection|array  $employees  Each item should contain at least:
     *         - id (employee_id)
     *         - name
     *         - employee_number (optional)
     *         - tin (optional)
     *         - fnpf_number (optional)
     *         - tax_code (P/S, default P)
     *         - is_resident (bool, default true)
     *         - basic
     *         - overtime (optional)
     *         - allowances (optional)
     *         - other_ordinary (optional)
     *         - deductions (optional)
     *         - bank_account / bank_code (optional, stored in calculation_details)
     * @param  PayFrequency|null  $frequency  Override run frequency if needed
     */
    public function process(PayrollRun $payrollRun, Collection|array $employees, ?PayFrequency $frequency = null): PayrollRun
    {
        $employees = collect($employees);
        $frequency = $frequency ?? $payrollRun->frequency ?? PayFrequency::Monthly;

        return DB::transaction(function () use ($payrollRun, $employees, $frequency) {
            // Clear existing slips if re-processing a draft/calculated run
            if ($payrollRun->isEditable()) {
                $payrollRun->salarySlips()->delete();
            }

            $totals = [
                'gross' => 0,
                'employee_fnpf' => 0,
                'employer_fnpf' => 0,
                'paye' => 0,
                'net' => 0,
                'employer_cost' => 0,
            ];

            foreach ($employees as $emp) {
                $emp = (array) $emp;

                $result = $this->calculator->calculate([
                    'basic'          => (float) ($emp['basic'] ?? 0),
                    'overtime'       => (float) ($emp['overtime'] ?? 0),
                    'allowances'     => (float) ($emp['allowances'] ?? 0),
                    'other_ordinary' => (float) ($emp['other_ordinary'] ?? 0),
                    'deductions'     => (float) ($emp['deductions'] ?? 0),
                    'is_resident'    => (bool)  ($emp['is_resident'] ?? true),
                    'frequency'      => $frequency,
                ]);

                $details = $result;
                // Preserve bank info for later export
                if (! empty($emp['bank_account'])) {
                    $details['bank_account'] = $emp['bank_account'];
                }
                if (! empty($emp['bank_code'])) {
                    $details['bank_code'] = $emp['bank_code'];
                }
                if (! empty($emp['bank_account_name'])) {
                    $details['bank_account_name'] = $emp['bank_account_name'];
                }

                SalarySlip::create([
                    'payroll_run_id'      => $payrollRun->id,
                    'employee_id'         => $emp['id'] ?? $emp['employee_id'] ?? 0,
                    'employee_name'       => $emp['name'] ?? $emp['employee_name'] ?? 'Unknown',
                    'employee_number'     => $emp['employee_number'] ?? null,
                    'tin'                 => $emp['tin'] ?? null,
                    'fnpf_number'         => $emp['fnpf_number'] ?? null,
                    'tax_code'            => $emp['tax_code'] ?? 'P',
                    'is_resident'         => (bool) ($emp['is_resident'] ?? true),
                    'basic'               => $result['gross'] > 0 ? ($emp['basic'] ?? 0) : 0,
                    'overtime'            => $emp['overtime'] ?? 0,
                    'allowances'          => $emp['allowances'] ?? 0,
                    'other_earnings'      => $emp['other_ordinary'] ?? 0,
                    'gross'               => $result['gross'],
                    'fnpf_base'           => $result['fnpf_base'],
                    'employee_fnpf'       => $result['employee_fnpf'],
                    'employer_fnpf'       => $result['employer_fnpf'],
                    'taxable_income'      => $result['taxable_income'],
                    'paye'                => $result['paye'],
                    'other_deductions'    => $result['other_deductions'],
                    'net_pay'             => $result['net_pay'],
                    'workcare_levy'       => $result['workcare_levy'],
                    'training_levy'       => $result['training_levy'],
                    'employer_cost'       => $result['employer_cost'],
                    'employee_fnpf_rate'  => $result['rates']['employee_fnpf_rate'],
                    'employer_fnpf_rate'  => $result['rates']['employer_fnpf_rate'],
                    'calculation_details' => $details,
                    'status'              => 'calculated',
                ]);

                $totals['gross']         += $result['gross'];
                $totals['employee_fnpf'] += $result['employee_fnpf'];
                $totals['employer_fnpf'] += $result['employer_fnpf'];
                $totals['paye']          += $result['paye'];
                $totals['net']           += $result['net_pay'];
                $totals['employer_cost'] += $result['employer_cost'];
            }

            $payrollRun->update([
                'status'               => PayrollStatus::Calculated,
                'employee_count'       => $employees->count(),
                'total_gross'          => round($totals['gross'], 2),
                'total_employee_fnpf'  => round($totals['employee_fnpf'], 2),
                'total_employer_fnpf'  => round($totals['employer_fnpf'], 2),
                'total_paye'           => round($totals['paye'], 2),
                'total_net'            => round($totals['net'], 2),
                'total_employer_cost'  => round($totals['employer_cost'], 2),
            ]);

            return $payrollRun->fresh(['salarySlips']);
        });
    }
}
