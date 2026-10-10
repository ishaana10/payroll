<?php

namespace Nuvis\FijiPayroll\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Nuvis\FijiPayroll\Models\PayrollRun;
use Nuvis\FijiPayroll\Services\Exports\FrcsTposExporter;

class GenerateFrcsTposJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public PayrollRun $payrollRun
    ) {}

    public function handle(FrcsTposExporter $exporter): void
    {
        $content  = $exporter->generate($this->payrollRun);
        $filename = $exporter->filename($this->payrollRun);

        Storage::disk('local')->put('fiji-payroll/exports/' . $filename, $content);
    }
}
