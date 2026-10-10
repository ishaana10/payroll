<?php

namespace Nuvis\FijiPayroll\Services\Exports\Banks;

use Nuvis\FijiPayroll\Models\PayrollRun;

interface BankFormatterInterface
{
    /**
     * Return the bank identifier (bsp, anz, hfc, etc.)
     */
    public function key(): string;

    /**
     * Human-readable bank name
     */
    public function name(): string;

    /**
     * Generate the file content (CSV or fixed-width text)
     */
    public function generate(PayrollRun $payrollRun): string;

    /**
     * Suggested filename
     */
    public function filename(PayrollRun $payrollRun): string;

    /**
     * MIME type / content type
     */
    public function mimeType(): string;
}
