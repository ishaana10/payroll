<?php

namespace Nuvis\FijiPayroll\Services\Exports\Banks;

use Nuvis\FijiPayroll\Models\PayrollRun;

/**
 * ANZ Fiji – Salary / Bulk Payment File Formatter
 *
 * Generates a CSV suitable for ANZ’s bulk payment upload.
 * Confirm exact field requirements with ANZ’s current specification
 * (some ANZ formats use fixed-width or specific date formats).
 */
class AnzFormatter implements BankFormatterInterface
{
    public function key(): string
    {
        return 'anz';
    }

    public function name(): string
    {
        return 'ANZ Fiji';
    }

    public function generate(PayrollRun $payrollRun): string
    {
        $payrollRun->loadMissing('salarySlips');

        $rows = [];

        // Common ANZ bulk payment style header
        $rows[] = [
            'Payment Date',
            'Account Number',
            'Account Name',
            'Amount',
            'Currency',
            'Particulars',
            'Analysis Code',
            'Reference',
        ];

        $payDate = $payrollRun->pay_date->format('Y-m-d');

        foreach ($payrollRun->salarySlips as $slip) {
            $details = $slip->calculation_details ?? [];
            $account = $details['bank_account'] ?? $details['anz_account'] ?? '';
            $accountName = $details['bank_account_name'] ?? $slip->employee_name;

            $rows[] = [
                $payDate,
                $this->cleanAccount($account),
                $this->cleanName($accountName),
                number_format($slip->net_pay, 2, '.', ''),
                'FJD',
                'Salary Payment',
                'SALARY',
                $payrollRun->reference . '-' . ($slip->employee_number ?? $slip->id),
            ];
        }

        return $this->toCsv($rows);
    }

    public function filename(PayrollRun $payrollRun): string
    {
        return 'ANZ_Salary_' . $payrollRun->reference . '_' . now()->format('Ymd_His') . '.csv';
    }

    public function mimeType(): string
    {
        return 'text/csv';
    }

    protected function cleanAccount(string $account): string
    {
        return preg_replace('/\D/', '', $account);
    }

    protected function cleanName(string $name): string
    {
        return substr(preg_replace('/[^A-Za-z0-9 \-]/', '', $name), 0, 50);
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
