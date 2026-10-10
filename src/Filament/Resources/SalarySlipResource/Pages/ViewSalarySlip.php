<?php

namespace Nuvis\FijiPayroll\Filament\Resources\SalarySlipResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Nuvis\FijiPayroll\Filament\Resources\SalarySlipResource;

class ViewSalarySlip extends ViewRecord
{
    protected static string $resource = SalarySlipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_payslip')
                ->label('Download Payslip PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn () => route('fiji-payroll.payslip.pdf', $this->record))
                ->openUrlInNewTab(),
        ];
    }
}
