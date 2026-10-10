# FijiPayroll Plugin for Nuvis ERP X – v4

Industrial-grade Fiji-compliant Payroll module.

## Highlights (v4)

- **Employee Multi-Select** on Process Payroll (no more JSON paste)
- **Optional earnings overrides** per employee via repeater
- **Bank formatters**: BSP, ANZ, HFC, BRED, Generic
- Full FNPF + PAYE/SRT engine, Payslip PDF, FRCS TPOS & FNPF exports

## Process Payroll (Multi-Select)

On any Draft / Calculated Payroll Run:

1. Click **Process Payroll**
2. Multi-select employees (searchable, preloaded)
3. Optionally expand “Earnings Overrides” to adjust basic / OT / allowances / deductions for this run only
4. Confirm → salary slips are calculated and created

The action reads from the model configured in:

```php
// config/fiji-payroll.php
'employee_model' => env('FIJI_EMPLOYEE_MODEL', \App\Models\Employee::class),
```

It best-effort maps common column names:

| Purpose            | Tried columns                          |
|--------------------|----------------------------------------|
| Name               | `name`, `first_name` + `last_name`     |
| Employee number    | `employee_number`, `staff_no`, `code`  |
| TIN                | `tin`, `tax_id`                        |
| FNPF number        | `fnpf_number`, `fnpf_no`               |
| Tax code           | `tax_code` (default `P`)               |
| Resident flag      | `is_resident` (default `true`)         |
| Basic salary       | `basic_salary`, `salary`, `basic`      |
| Bank account       | `bank_account`, `account_number`       |
| Bank code          | `bank_code`, `bank`                    |

## Bank Formatters

| Key       | Bank                     |
|-----------|--------------------------|
| `bsp`     | Bank of South Pacific    |
| `anz`     | ANZ Fiji                 |
| `hfc`     | HFC Bank (Fiji)          |
| `bred`    | BRED Bank (Fiji)         |
| `generic` | Generic / Other Bank     |

```php
GenerateBankFileJob::dispatch($payrollRun, 'hfc');
// or
$manager = app(\Nuvis\FijiPayroll\Services\Exports\Banks\BankFormatterManager::class);
$result = $manager->generate('hfc', $payrollRun);
```

All export actions appear in the **Exports** dropdown on the Payroll Run view page.

## Configuration

```bash
php artisan vendor:publish --tag=fiji-payroll-config
```

Key env vars:
- `FIJI_EMPLOYEE_MODEL` – Eloquent model class for employees
- `FIJI_EMPLOYER_TIN` – for FRCS TPOS export
- `FIJI_EMPLOYER_FNPF_REF` – for FNPF schedule

## Installation

1. Place in `plugins/` or require via Composer path repo
2. Register `FijiPayrollPlugin` in your Filament Panel Provider
3. `composer require barryvdh/laravel-dompdf` (payslips)
4. `php artisan migrate`
5. Ensure your Employee model/table has the columns you need (or adjust mapping)

Exports are written to `storage/app/fiji-payroll/exports/`.

---

Built for Nuvis ERP X • Nuvis Technologies (Fiji)
