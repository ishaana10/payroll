<?php

namespace Nuvis\FijiPayroll\Http\Controllers;

use Illuminate\Routing\Controller;
use Nuvis\FijiPayroll\Models\SalarySlip;
use Barryvdh\DomPDF\Facade\Pdf; // requires barryvdh/laravel-dompdf

class PayslipController extends Controller
{
    public function pdf(SalarySlip $salarySlip)
    {
        $salarySlip->load('payrollRun');

        $pdf = Pdf::loadView('fiji-payroll::payslip', [
            'slip' => $salarySlip,
            'companyName' => config('app.name', 'Your Company Ltd'),
        ])->setPaper('a4');

        $filename = 'Payslip_' . str_replace(' ', '_', $salarySlip->employee_name) . '_' . $salarySlip->payrollRun->reference . '.pdf';

        return $pdf->download($filename);
    }
}
