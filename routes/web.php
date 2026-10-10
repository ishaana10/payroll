<?php

use Illuminate\Support\Facades\Route;
use Nuvis\FijiPayroll\Http\Controllers\PayslipController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/fiji-payroll/payslip/{salarySlip}/pdf', [PayslipController::class, 'pdf'])
        ->name('fiji-payroll.payslip.pdf');
});
