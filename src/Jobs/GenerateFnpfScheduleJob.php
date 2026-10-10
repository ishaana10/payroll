<?php

namespace Nuvis\FijiPayroll\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Nuvis\FijiPayroll\Models\PayrollRun;

/**
 * Generate FNPF Contribution Schedule file for a Payroll Run.
 *
 * Produces a simple CSV that can be adapted to the current FNPF online
 * portal upload format. Expand the columns to match the exact
 * specification published by FNPF at the time of use.
 */
class GenerateFnpfScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public PayrollRun $payrollRun
    ) {}

    public function handle(): void
    {
        $this->payrollRun->load('salarySlips');

        $lines = [];
        // Header – adjust to match current FNPF template
        $lines[] = [
            'Employer Reference',
            'Period Start',
            'Period End',
            'Employee FNPF Number',
            'Employee Name',
            'TIN',
            'Gross Wages (FNPF Base)',
            'Employee Contribution (8%)',
            'Employer Contribution (8%)',
            'Total Contribution',
        ];

        foreach ($this->payrollRun->salarySlips as $slip) {
            $lines[] = [
                config('fiji-payroll.employer_fnpf_reference', 'EMP-REF'),
                $this->payrollRun->period_start->format('Y-m-d'),
                $this->payrollRun->period_end->format('Y-m-d'),
                $slip->fnpf_number,
                $slip->employee_name,
                $slip->tin,
                number_format($slip->fnpf_base, 2, '.', ''),
                number_format($slip->employee_fnpf, 2, '.', ''),
                number_format($slip->employer_fnpf, 2, '.', ''),
                number_format($slip->employee_fnpf + $slip->employer_fnpf, 2, '.', ''),
            ];
        }

        $csv = $this->arrayToCsv($lines);
        $filename = 'FNPF_Schedule_' . $this->payrollRun->reference . '_' . now()->format('Ymd_His') . '.csv';

        Storage::disk('local')->put('fiji-payroll/exports/' . $filename, $csv);

        // Optional: notify user, store path on the PayrollRun, etc.
        // $this->payrollRun->update(['fnpf_export_path' => $filename]);
    }

    protected function arrayToCsv(array $rows): string
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
