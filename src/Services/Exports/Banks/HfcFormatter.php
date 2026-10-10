<?php

namespace Nuvis\FijiPayroll\Services\Exports\Banks;

use Nuvis\FijiPayroll\Models\PayrollRun;

/**
 * HFC Bank (Fiji) – Salary Credit File Formatter
 *
 * Practical CSV for HFC bulk salary / credit uploads.
 * Confirm column order against HFC’s current template.
 */
class HfcFormatter implements BankFormatterInterface
{
    public function key(): string
    {
        return 'hfc';
    }

    public function name(): string
    {
        return 'HFC Bank (Fiji)';
    }

    public function generate(PayrollRun $payrollRun): string
    {
        $payrollRun->loadMissing('salarySlips');

        $rows = [];
        $rows[] = [
            'Account Number',
            'Beneficiary Name',
            'Amount',
            'Narration',
            'Reference',
            'Value Date',
        ];

        $valueDate = $payrollRun->pay_date->format('Y-m-d');

        foreach ($payrollRun->salarySlips as $slip) {
            $details = $slip->calculation_details ?? [];
            $account = $details['bank_account'] ?? $details['hfc_account'] ?? '';
            $name    = $details['bank_account_name'] ?? $slip->employee_name;

            $rows[] = [
                $this->cleanAccount($account),
                $this->cleanName($name),
                number_format($slip->net_pay, 2, '.', ''),
                'SALARY ' . $payrollRun->reference,
                $payrollRun->reference . '-' . ($slip->employee_number ?? $slip->id),
                $valueDate,
            ];
        }

        return $this->toCsv($rows);
    }

    public function filename(PayrollRun $payrollRun): string
    {
        return 'HFC_Salary_' . $payrollRun->reference . '_' . now()->format('Ymd_His') . '.csv';
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
        return substr(preg_replace('/[^A-Za-z0-9 \-]/', '', $name), 0, 40);
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
