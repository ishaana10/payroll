<?php

namespace Nuvis\FijiPayroll\Services\Exports;

use Nuvis\FijiPayroll\Models\PayrollRun;

/**
 * FRCS TPOS (Taxpayer Online Service) PAYE Export
 *
 * Generates a CSV structured for FRCS PAYE / payday reporting.
 * Column layout is based on commonly required fields for Fiji PAYE
 * submissions. Always verify against the latest FRCS TPOS template
 * / Pay Day Reporting Template before live use.
 */
class FrcsTposExporter
{
    public function generate(PayrollRun $payrollRun): string
    {
        $payrollRun->loadMissing('salarySlips');

        $rows = [];

        // Header aligned with typical FRCS PAYE / payday reporting needs
        $rows[] = [
            'Employer TIN',
            'Payroll Period Start',
            'Payroll Period End',
            'Pay Date',
            'Employee TIN',
            'Employee Name',
            'Tax Code',          // P or S
            'Resident (Y/N)',
            'Gross Earnings',
            'FNPF Deducted (Employee)',
            'Taxable Income',
            'PAYE Deducted',
            'Other Deductions',
            'Net Pay',
            'Employer FNPF',
            'Employee Number',
            'FNPF Number',
        ];

        $employerTin = config('fiji-payroll.employer_tin', '');

        foreach ($payrollRun->salarySlips as $slip) {
            $rows[] = [
                $employerTin,
                $payrollRun->period_start->format('Y-m-d'),
                $payrollRun->period_end->format('Y-m-d'),
                $payrollRun->pay_date->format('Y-m-d'),
                $slip->tin ?? '',
                $slip->employee_name,
                $slip->tax_code ?? 'P',
                $slip->is_resident ? 'Y' : 'N',
                number_format($slip->gross, 2, '.', ''),
                number_format($slip->employee_fnpf, 2, '.', ''),
                number_format($slip->taxable_income, 2, '.', ''),
                number_format($slip->paye, 2, '.', ''),
                number_format($slip->other_deductions, 2, '.', ''),
                number_format($slip->net_pay, 2, '.', ''),
                number_format($slip->employer_fnpf, 2, '.', ''),
                $slip->employee_number ?? '',
                $slip->fnpf_number ?? '',
            ];
        }

        return $this->toCsv($rows);
    }

    public function filename(PayrollRun $payrollRun): string
    {
        return 'FRCS_TPOS_PAYE_' . $payrollRun->reference . '_' . now()->format('Ymd_His') . '.csv';
    }

    protected function toCsv(array $rows): string
    {
        $fh = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($fh, $row);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);
        return $csv;
    }
}
