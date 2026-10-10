<?php

namespace Nuvis\FijiPayroll\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Nuvis\FijiPayroll\Models\PayrollRun;
use Nuvis\FijiPayroll\Services\Exports\Banks\BankFormatterManager;

/**
 * Generate bank-specific salary credit file using registered formatters.
 */
class GenerateBankFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public PayrollRun $payrollRun,
        public string $bank = 'bsp' // bsp | anz
    ) {}

    public function handle(BankFormatterManager $manager): void
    {
        $result = $manager->generate($this->bank, $this->payrollRun);

        Storage::disk('local')->put(
            'fiji-payroll/exports/' . $result['filename'],
            $result['content']
        );
    }
}
