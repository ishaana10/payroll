<?php

namespace Nuvis\FijiPayroll\Filament\Resources\PayrollRunResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Nuvis\FijiPayroll\Filament\Resources\PayrollRunResource;

class ListPayrollRuns extends ListRecords
{
    protected static string $resource = PayrollRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
