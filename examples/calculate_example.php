<?php

/**
 * Standalone example of the Fiji Payroll calculation classes.
 * Run with: php examples/calculate_example.php
 * (Requires the classes to be autoloaded or include the files manually)
 */

require_once __DIR__ . '/../src/Enums/PayFrequency.php';
require_once __DIR__ . '/../src/Services/FnpfService.php';
require_once __DIR__ . '/../src/Services/PayeCalculator.php';
require_once __DIR__ . '/../src/Services/PayrollCalculator.php';

// Mock config for standalone run
if (! function_exists('config')) {
    function config($key, $default = null) {
        $config = [
            'fiji-payroll.fnpf.employee_rate' => 0.08,
            'fiji-payroll.fnpf.employer_rate' => 0.08,
            'fiji-payroll.fnpf.include_allowances' => true,
            'fiji-payroll.levies.enable_workcare' => true,
            'fiji-payroll.levies.workcare_rate' => 0.01,
            'fiji-payroll.levies.enable_training_levy' => false,
        ];
        return $config[$key] ?? $default;
    }
}

use Nuvis\FijiPayroll\Enums\PayFrequency;
use Nuvis\FijiPayroll\Services\FnpfService;
use Nuvis\FijiPayroll\Services\PayeCalculator;
use Nuvis\FijiPayroll\Services\PayrollCalculator;

$calculator = new PayrollCalculator(
    new FnpfService(),
    new PayeCalculator()
);

// Example 1: Simple monthly salary FJD 3,000
echo "=== Example 1: Monthly Gross FJD 3,000 (Resident) ===\n";
$result1 = $calculator->exampleMonthly(3000.00);
print_r($result1);

// Example 2: With overtime and allowances
echo "\n=== Example 2: Basic 2,800 + OT 350 + Allowances 200 ===\n";
$result2 = $calculator->calculate([
    'basic' => 2800,
    'overtime' => 350,
    'allowances' => 200,
    'is_resident' => true,
    'frequency' => PayFrequency::Monthly,
]);
print_r($result2);

// Example 3: Higher earner (to show progressive tax)
echo "\n=== Example 3: Monthly Gross FJD 8,000 (higher tax band) ===\n";
$result3 = $calculator->exampleMonthly(8000.00);
print_r($result3);
