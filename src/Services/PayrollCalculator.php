<?php

namespace Nuvis\FijiPayroll\Services;

use Nuvis\FijiPayroll\Enums\PayFrequency;

/**
 * Main Fiji Payroll Calculator
 *
 * Orchestrates FNPF + PAYE (with SRT) + levies to produce a complete payslip calculation.
 */
class PayrollCalculator
{
    public function __construct(
        protected FnpfService $fnpf,
        protected PayeCalculator $paye
    ) {}

    /**
     * Calculate a full payroll result for one employee for one period.
     */
    public function calculate(array $input): array
    {
        $basic        = (float) ($input['basic'] ?? 0);
        $overtime     = (float) ($input['overtime'] ?? 0);
        $allowances   = (float) ($input['allowances'] ?? 0);
        $other        = (float) ($input['other_ordinary'] ?? 0);
        $otherDeds    = (float) ($input['deductions'] ?? 0);
        $isResident   = (bool)  ($input['is_resident'] ?? true);
        $frequency    = $input['frequency'] ?? PayFrequency::Monthly;

        $gross = round($basic + $overtime + $allowances + $other, 2);

        $fnpfBase = $this->fnpf->getEligibleWageBase([
            'basic' => $basic,
            'overtime' => $overtime,
            'allowances' => $allowances,
            'other_ordinary' => $other,
        ]);

        $employeeFnpf = $this->fnpf->calculateEmployeeContribution($fnpfBase);
        $employerFnpf = $this->fnpf->calculateEmployerContribution($fnpfBase);

        // Employee FNPF is deductible for PAYE purposes
        $taxableIncome = max(0, $gross - $employeeFnpf);

        $payeResult = $this->paye->calculate(
            taxableIncome: $taxableIncome,
            frequency: $frequency,
            isResident: $isResident
        );

        $payeAmount = $payeResult['paye'];

        $workcare = 0.0;
        $trainingLevy = 0.0;

        if (config('fiji-payroll.levies.enable_workcare', true)) {
            $workcare = round($gross * config('fiji-payroll.levies.workcare_rate', 0.01), 2);
        }

        if (config('fiji-payroll.levies.enable_training_levy', false)) {
            $trainingLevy = round($gross * config('fiji-payroll.levies.training_levy_rate', 0.01), 2);
        }

        $netPay = round($gross - $employeeFnpf - $payeAmount - $otherDeds, 2);
        $employerCost = round($gross + $employerFnpf + $workcare + $trainingLevy, 2);

        return [
            'gross' => $gross,
            'fnpf_base' => $fnpfBase,
            'employee_fnpf' => $employeeFnpf,
            'employer_fnpf' => $employerFnpf,
            'total_fnpf' => $employeeFnpf + $employerFnpf,
            'taxable_income' => $taxableIncome,
            'paye' => $payeAmount,
            'basic_tax' => $payeResult['basic_tax'] ?? $payeAmount,
            'srt' => $payeResult['srt'] ?? 0.0,
            'other_deductions' => $otherDeds,
            'net_pay' => $netPay,
            'workcare_levy' => $workcare,
            'training_levy' => $trainingLevy,
            'employer_cost' => $employerCost,
            'paye_details' => $payeResult,
            'rates' => [
                'employee_fnpf_rate' => $this->fnpf->getEmployeeRate(),
                'employer_fnpf_rate' => $this->fnpf->getEmployerRate(),
            ],
        ];
    }

    public function exampleMonthly(float $grossSalary): array
    {
        return $this->calculate([
            'basic' => $grossSalary,
            'is_resident' => true,
            'frequency' => PayFrequency::Monthly,
        ]);
    }
}
