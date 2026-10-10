<?php

namespace Nuvis\FijiPayroll\Services\Exports\Banks;

use Nuvis\FijiPayroll\Models\PayrollRun;

/**
 * BRED Bank (Fiji) – Salary Credit File Formatter
 */
class BredFormatter implements BankFormatterInterface
{
    public function key(): string
    {
        return 'bred';
    }

    public function name(): string
    {
        return 'BRED Bank (Fiji)';
    }

    public function generate(PayrollRun $payrollRun): string
    {
        $payrollRun->loadMissing('salarySlips');

        $rows = [];
        $rows[] = [
            'Account Number',
            'Account Name',
            'Amount',
            'Currency',
            'Particulars',
            'Reference',
        ];

        foreach ($payrollRun->salarySlips as $slip) {
            $details = $slip->calculation_details ?? [];
            $account = $details['bank_account'] ?? '';
            $name    = $details['bank_account_name'] ?? $slip->employee_name;

            $rows[] = [
                preg_replace('/\D/', '', $account),
                substr(preg_replace('/[^A-Za-z0-9 \-]/', '', $name), 0, 40),
                number_format($slip->net_pay, 2, '.', ''),
                'FJD',
                'Salary',
                $payrollRun->reference . '-' . ($slip->employee_number ?? $slip->id),
            ];
        }

        return $this->toCsv($rows);
    }

    public function filename(PayrollRun $payrollRun): string
    {
        return 'BRED_Salary_' . $payrollRun->reference . '_' . now()->format('Ymd_His') . '.csv';
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
