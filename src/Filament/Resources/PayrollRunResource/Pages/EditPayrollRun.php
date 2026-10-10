<?php

namespace Nuvis\FijiPayroll\Filament\Resources\PayrollRunResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Nuvis\FijiPayroll\Filament\Resources\PayrollRunResource;

class EditPayrollRun extends EditRecord
{
    protected static string $resource = PayrollRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
