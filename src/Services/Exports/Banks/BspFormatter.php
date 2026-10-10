<?php

namespace Nuvis\FijiPayroll\Services\Exports\Banks;

use Nuvis\FijiPayroll\Models\PayrollRun;

/**
 * Bank of South Pacific (BSP) Fiji – Salary Credit File Formatter
 *
 * Produces a practical CSV that can be mapped to BSP’s bulk payment /
 * salary upload template. Adjust column order and headers to the exact
 * current BSP specification when integrating.
 */
class BspFormatter implements BankFormatterInterface
{
    public function key(): string
    {
        return 'bsp';
    }

    public function name(): string
    {
        return 'Bank of South Pacific (BSP)';
    }

    public function generate(PayrollRun $payrollRun): string
    {
        $payrollRun->loadMissing('salarySlips');

        $rows = [];

        // Header row – adapt to current BSP bulk credit template
        $rows[] = [
            'Account Number',
            'Account Name',
            'Amount',
            'Particulars',
            'Code',
            'Reference',
        ];

        foreach ($payrollRun->salarySlips as $slip) {
            $details = $slip->calculation_details ?? [];
            $account = $details['bank_account'] ?? $details['bsp_account'] ?? '';
            $accountName = $details['bank_account_name'] ?? $slip->employee_name;

            $rows[] = [
                $this->cleanAccount($account),
                $this->cleanName($accountName),
                number_format($slip->net_pay, 2, '.', ''),
                'SALARY',
                'SAL',
                $payrollRun->reference . '-' . ($slip->employee_number ?? $slip->id),
            ];
        }

        return $this->toCsv($rows);
    }

    public function filename(PayrollRun $payrollRun): string
    {
        return 'BSP_Salary_' . $payrollRun->reference . '_' . now()->format('Ymd_His') . '.csv';
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
