<?php

namespace Nuvis\FijiPayroll\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Nuvis\FijiPayroll\Models\PayrollRun;
use Nuvis\FijiPayroll\Services\PayrollProcessor;

/**
 * Process Payroll Action with Employee Multi-Select
 *
 * Uses a configurable Employee model (default: App\Models\Employee).
 * Falls back gracefully if the model or expected columns do not exist.
 *
 * Expected employee columns (best effort):
 * id, name / first_name+last_name, employee_number, tin, fnpf_number,
 * tax_code, is_resident, basic_salary / salary, bank_account, bank_code
 */
class ProcessPayrollAction
{
    public static function make(): Action
    {
        $employeeModel = config('fiji-payroll.employee_model', \App\Models\Employee::class);

        return Action::make('processPayroll')
            ->label('Process Payroll')
            ->icon('heroicon-o-calculator')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Process Payroll Run')
            ->modalDescription('Select employees and (optionally) override earnings. FNPF + PAYE will be calculated and salary slips created.')
            ->modalWidth('4xl')
            ->form([
                Forms\Components\Select::make('employee_ids')
                    ->label('Employees')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required()
                    ->options(function () use ($employeeModel) {
                        return self::employeeOptions($employeeModel);
                    })
                    ->helperText('Select one or more employees to include in this payroll run.'),

                Forms\Components\Section::make('Optional Earnings Overrides')
                    ->description('Leave blank to use the employee’s default basic salary. Overrides apply only to this run.')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Repeater::make('overrides')
                            ->label('Overrides')
                            ->schema([
                                Forms\Components\Select::make('employee_id')
                                    ->label('Employee')
                                    ->options(function () use ($employeeModel) {
                                        return self::employeeOptions($employeeModel);
                                    })
                                    ->required()
                                    ->searchable(),
                                Forms\Components\TextInput::make('basic')
                                    ->label('Basic')
                                    ->numeric()
                                    ->prefix('FJD'),
                                Forms\Components\TextInput::make('overtime')
                                    ->label('Overtime')
                                    ->numeric()
                                    ->prefix('FJD')
                                    ->default(0),
                                Forms\Components\TextInput::make('allowances')
                                    ->label('Allowances')
                                    ->numeric()
                                    ->prefix('FJD')
                                    ->default(0),
                                Forms\Components\TextInput::make('deductions')
                                    ->label('Other Deductions')
                                    ->numeric()
                                    ->prefix('FJD')
                                    ->default(0),
                            ])
                            ->columns(5)
                            ->defaultItems(0)
                            ->addActionLabel('Add override')
                            ->collapsible(),
                    ]),
            ])
            ->action(function (array $data, PayrollRun $record) use ($employeeModel) {
                if (! $record->isEditable()) {
                    Notification::make()
                        ->title('Cannot process')
                        ->body('Only Draft or Calculated runs can be re-processed.')
                        ->danger()
                        ->send();
                    return;
                }

                $employeeIds = $data['employee_ids'] ?? [];
                if (empty($employeeIds)) {
                    Notification::make()
                        ->title('No employees selected')
                        ->danger()
                        ->send();
                    return;
                }

                $overridesById = collect($data['overrides'] ?? [])
                    ->keyBy('employee_id');

                try {
                    $employees = self::loadEmployees($employeeModel, $employeeIds, $overridesById);

                    if ($employees->isEmpty()) {
                        Notification::make()
                            ->title('No valid employees found')
                            ->body('Could not load the selected employees. Check the employee_model config and table structure.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $processor = app(PayrollProcessor::class);
                    $processor->process($record, $employees);

                    Notification::make()
                        ->title('Payroll processed successfully')
                        ->body($employees->count() . ' salary slip(s) created/updated.')
                        ->success()
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('Processing failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->visible(fn (PayrollRun $record) => $record->isEditable());
    }

    /**
     * Build selectable options from the Employee model.
     */
    protected static function employeeOptions(string $modelClass): array
    {
        if (! class_exists($modelClass)) {
            return [];
        }

        try {
            /** @var Model $model */
            $query = $modelClass::query();

            // Prefer active employees if a status/active column exists
            if (self::hasColumn($modelClass, 'is_active')) {
                $query->where('is_active', true);
            } elseif (self::hasColumn($modelClass, 'status')) {
                $query->where('status', 'active');
            }

            $rows = $query->orderBy(
                self::hasColumn($modelClass, 'name') ? 'name' : 'id'
            )->get();

            $options = [];
            foreach ($rows as $emp) {
                $label = self::employeeLabel($emp);
                $options[$emp->getKey()] = $label;
            }
            return $options;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Load full employee records and merge any overrides.
     */
    protected static function loadEmployees(string $modelClass, array $ids, $overridesById)
    {
        if (! class_exists($modelClass)) {
            return collect();
        }

        $records = $modelClass::query()->whereIn((new $modelClass)->getKeyName(), $ids)->get();

        return $records->map(function ($emp) use ($overridesById) {
            $override = $overridesById->get($emp->getKey(), []);

            $name = self::employeeName($emp);

            $basic = $override['basic']
                ?? $emp->basic_salary
                ?? $emp->salary
                ?? $emp->basic
                ?? 0;

            return [
                'id'                => $emp->getKey(),
                'name'              => $name,
                'employee_number'   => $emp->employee_number ?? $emp->staff_no ?? $emp->code ?? null,
                'tin'               => $emp->tin ?? $emp->tax_id ?? null,
                'fnpf_number'       => $emp->fnpf_number ?? $emp->fnpf_no ?? null,
                'tax_code'          => $emp->tax_code ?? 'P',
                'is_resident'       => $emp->is_resident ?? true,
                'basic'             => (float) $basic,
                'overtime'          => (float) ($override['overtime'] ?? 0),
                'allowances'        => (float) ($override['allowances'] ?? 0),
                'deductions'        => (float) ($override['deductions'] ?? 0),
                'bank_account'      => $emp->bank_account ?? $emp->account_number ?? null,
                'bank_code'         => $emp->bank_code ?? $emp->bank ?? null,
                'bank_account_name' => $emp->bank_account_name ?? $name,
            ];
        });
    }

    protected static function employeeLabel(Model $emp): string
    {
        $name = self::employeeName($emp);
        $number = $emp->employee_number ?? $emp->staff_no ?? $emp->code ?? null;
        return $number ? "{$name} ({$number})" : $name;
    }

    protected static function employeeName(Model $emp): string
    {
        if (! empty($emp->name)) {
            return $emp->name;
        }
        $first = $emp->first_name ?? $emp->firstname ?? '';
        $last  = $emp->last_name ?? $emp->lastname ?? '';
        $full  = trim("$first $last");
        return $full !== '' ? $full : 'Employee #' . $emp->getKey();
    }

    protected static function hasColumn(string $modelClass, string $column): bool
    {
        try {
            $model = new $modelClass;
            return \Illuminate\Support\Facades\Schema::hasColumn($model->getTable(), $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
