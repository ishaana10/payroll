<?php

namespace Nuvis\FijiPayroll\Filament\Resources\PayrollRunResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Nuvis\FijiPayroll\Models\SalarySlip;

class SalarySlipsRelationManager extends RelationManager
{
    protected static string $relationship = 'salarySlips';

    protected static ?string $title = 'Salary Slips';

    protected static ?string $recordTitleAttribute = 'employee_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('employee_name')->disabled(),
                Forms\Components\TextInput::make('gross')->numeric()->prefix('FJD')->disabled(),
                Forms\Components\TextInput::make('employee_fnpf')->numeric()->prefix('FJD')->disabled(),
                Forms\Components\TextInput::make('paye')->numeric()->prefix('FJD')->disabled(),
                Forms\Components\TextInput::make('net_pay')->numeric()->prefix('FJD')->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('employee_name')
            ->columns([
                Tables\Columns\TextColumn::make('employee_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('employee_number')->toggleable(),
                Tables\Columns\TextColumn::make('gross')->money('FJD'),
                Tables\Columns\TextColumn::make('employee_fnpf')->label('Emp FNPF')->money('FJD'),
                Tables\Columns\TextColumn::make('employer_fnpf')->label('Er FNPF')->money('FJD')->toggleable(),
                Tables\Columns\TextColumn::make('paye')->money('FJD'),
                Tables\Columns\TextColumn::make('net_pay')->money('FJD')->weight('bold'),
                Tables\Columns\TextColumn::make('employer_cost')->money('FJD')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (SalarySlip $record) => route('fiji-payroll.payslip.pdf', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }
}
