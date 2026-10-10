<?php

namespace Nuvis\FijiPayroll\Services\Exports\Banks;

use Nuvis\FijiPayroll\Models\PayrollRun;

/**
 * Generic / Other Bank – simple salary credit CSV
 * Use when the bank is not BSP, ANZ, HFC or BRED.
 */
class GenericFormatter implements BankFormatterInterface
{
    public function key(): string
    {
        return 'generic';
    }

    public function name(): string
    {
        return 'Generic / Other Bank';
    }

    public function generate(PayrollRun $payrollRun): string
    {
        $payrollRun->loadMissing('salarySlips');

        $rows = [];
        $rows[] = [
            'Employee Name',
            'Employee Number',
            'Bank Account Number',
            'Bank Code',
            'Amount (FJD)',
            'Particulars',
            'Payroll Reference',
        ];

        foreach ($payrollRun->salarySlips as $slip) {
            $details = $slip->calculation_details ?? [];

            $rows[] = [
                $slip->employee_name,
                $slip->employee_number ?? '',
                preg_replace('/\D/', '', $details['bank_account'] ?? ''),
                $details['bank_code'] ?? 'OTHER',
                number_format($slip->net_pay, 2, '.', ''),
                'Salary ' . $payrollRun->reference,
                $payrollRun->reference,
            ];
        }

        return $this->toCsv($rows);
    }

    public function filename(PayrollRun $payrollRun): string
    {
        return 'Generic_Salary_' . $payrollRun->reference . '_' . now()->format('Ymd_His') . '.csv';
    }

    public function mimeType(): string
    {
        return 'text/csv';
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
