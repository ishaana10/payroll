<?php

namespace Nuvis\FijiPayroll\Services;

/**
 * Fiji National Provident Fund (FNPF) Calculation Service
 *
 * Current rates (1 Aug 2026 – 31 Jul 2027):
 * - Employee: 8%
 * - Employer: 8% (temporary reduction from 10%)
 *
 * Rates are configurable via config/fiji-payroll.php
 */
class FnpfService
{
    public function getEmployeeRate(): float
    {
        return (float) config('fiji-payroll.fnpf.employee_rate', 0.08);
    }

    public function getEmployerRate(): float
    {
        return (float) config('fiji-payroll.fnpf.employer_rate', 0.08);
    }

    /**
     * Calculate employee FNPF contribution (deducted from gross)
     */
    public function calculateEmployeeContribution(float $grossWages): float
    {
        return round($grossWages * $this->getEmployeeRate(), 2);
    }

    /**
     * Calculate employer FNPF contribution (employer cost)
     */
    public function calculateEmployerContribution(float $grossWages): float
    {
        return round($grossWages * $this->getEmployerRate(), 2);
    }

    /**
     * Total FNPF contribution (employee + employer)
     */
    public function calculateTotalContribution(float $grossWages): float
    {
        return $this->calculateEmployeeContribution($grossWages)
             + $this->calculateEmployerContribution($grossWages);
    }

    /**
     * Determine the FNPF-eligible wage base.
     * Most ordinary wages, overtime and regular allowances are included.
     * Confirm exact inclusions with current FNPF guidelines.
     */
    public function getEligibleWageBase(array $components): float
    {
        $base = $components['basic'] ?? 0;
        $base += $components['overtime'] ?? 0;

        if (config('fiji-payroll.fnpf.include_allowances', true)) {
            $base += $components['allowances'] ?? 0;
        }

        // Add other taxable / ordinary components as needed
        $base += $components['other_ordinary'] ?? 0;

        return round($base, 2);
    }
}
