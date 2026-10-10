<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip - {{ $slip->employee_name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e3a5f; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; color: #1e3a5f; }
        .header p { margin: 2px 0; color: #555; }
        .section { margin-bottom: 15px; }
        .section-title { background: #1e3a5f; color: #fff; padding: 5px 8px; font-weight: bold; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 4px 6px; }
        .label { width: 45%; color: #444; }
        .amount { text-align: right; font-family: monospace; }
        .total-row { font-weight: bold; border-top: 1px solid #ccc; }
        .net-pay { background: #e8f5e9; font-size: 14px; font-weight: bold; }
        .footer { margin-top: 30px; font-size: 10px; color: #777; text-align: center; border-top: 1px solid #ddd; padding-top: 8px; }
        .two-col { width: 48%; display: inline-block; vertical-align: top; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName ?? 'Company Name' }}</h1>
        <p>PAYSLIP</p>
        <p>{{ $slip->payrollRun->title ?? $slip->payrollRun->reference }}</p>
        <p>Pay Period: {{ $slip->payrollRun->period_start->format('d M Y') }} – {{ $slip->payrollRun->period_end->format('d M Y') }}</p>
        <p>Pay Date: {{ $slip->payrollRun->pay_date->format('d M Y') }}</p>
    </div>

    <div class="section">
        <div class="section-title">Employee Details</div>
        <table>
            <tr>
                <td class="label">Name</td>
                <td>{{ $slip->employee_name }}</td>
                <td class="label">Employee No.</td>
                <td>{{ $slip->employee_number }}</td>
            </tr>
            <tr>
                <td class="label">TIN</td>
                <td>{{ $slip->tin }}</td>
                <td class="label">FNPF No.</td>
                <td>{{ $slip->fnpf_number }}</td>
            </tr>
            <tr>
                <td class="label">Tax Code</td>
                <td>{{ $slip->tax_code }}</td>
                <td class="label">Resident</td>
                <td>{{ $slip->is_resident ? 'Yes' : 'No' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Earnings</div>
        <table>
            <tr><td class="label">Basic Salary</td><td class="amount">{{ number_format($slip->basic, 2) }}</td></tr>
            <tr><td class="label">Overtime</td><td class="amount">{{ number_format($slip->overtime, 2) }}</td></tr>
            <tr><td class="label">Allowances</td><td class="amount">{{ number_format($slip->allowances, 2) }}</td></tr>
            <tr><td class="label">Other Earnings</td><td class="amount">{{ number_format($slip->other_earnings, 2) }}</td></tr>
            <tr class="total-row"><td class="label">Gross Pay</td><td class="amount">{{ number_format($slip->gross, 2) }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Deductions</div>
        <table>
            <tr><td class="label">Employee FNPF ({{ number_format($slip->employee_fnpf_rate * 100, 1) }}%)</td><td class="amount">{{ number_format($slip->employee_fnpf, 2) }}</td></tr>
            <tr><td class="label">PAYE</td><td class="amount">{{ number_format($slip->paye, 2) }}</td></tr>
            <tr><td class="label">Other Deductions</td><td class="amount">{{ number_format($slip->other_deductions, 2) }}</td></tr>
            <tr class="total-row"><td class="label">Total Deductions</td><td class="amount">{{ number_format($slip->employee_fnpf + $slip->paye + $slip->other_deductions, 2) }}</td></tr>
        </table>
    </div>

    <div class="section">
        <table>
            <tr class="net-pay">
                <td class="label">NET PAY</td>
                <td class="amount">FJD {{ number_format($slip->net_pay, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Employer Contributions (for information)</div>
        <table>
            <tr><td class="label">Employer FNPF ({{ number_format($slip->employer_fnpf_rate * 100, 1) }}%)</td><td class="amount">{{ number_format($slip->employer_fnpf, 2) }}</td></tr>
            <tr><td class="label">WorkCare Levy</td><td class="amount">{{ number_format($slip->workcare_levy, 2) }}</td></tr>
            <tr><td class="label">Training Levy</td><td class="amount">{{ number_format($slip->training_levy, 2) }}</td></tr>
            <tr class="total-row"><td class="label">Total Employer Cost</td><td class="amount">{{ number_format($slip->employer_cost, 2) }}</td></tr>
        </table>
    </div>

    <div class="footer">
        This is a computer-generated payslip. For queries contact your HR / Payroll department.<br>
        Generated on {{ now()->format('d M Y H:i') }} • Nuvis ERP X – FijiPayroll
    </div>
</body>
</html>
