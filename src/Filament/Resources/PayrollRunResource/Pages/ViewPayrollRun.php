<?php

namespace Nuvis\FijiPayroll\Filament\Resources\PayrollRunResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Nuvis\FijiPayroll\Filament\Actions\ProcessPayrollAction;
use Nuvis\FijiPayroll\Filament\Resources\PayrollRunResource;
use Nuvis\FijiPayroll\Jobs\GenerateBankFileJob;
use Nuvis\FijiPayroll\Jobs\GenerateFnpfScheduleJob;
use Nuvis\FijiPayroll\Jobs\GenerateFrcsTposJob;
use Nuvis\FijiPayroll\Models\PayrollRun;

class ViewPayrollRun extends ViewRecord
{
    protected static string $resource = PayrollRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ProcessPayrollAction::make(),

            Actions\ActionGroup::make([
                Actions\Action::make('export_fnpf')
                    ->label('FNPF Schedule')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (PayrollRun $record) {
                        GenerateFnpfScheduleJob::dispatch($record);
                        \Filament\Notifications\Notification::make()
                            ->title('FNPF schedule export queued')
                            ->success()->send();
                    }),

                Actions\Action::make('export_frcs')
                    ->label('FRCS TPOS PAYE')
                    ->icon('heroicon-o-document-text')
                    ->action(function (PayrollRun $record) {
                        GenerateFrcsTposJob::dispatch($record);
                        \Filament\Notifications\Notification::make()
                            ->title('FRCS TPOS export queued')
                            ->success()->send();
                    }),

                Actions\Action::make('export_bsp')
                    ->label('Bank File – BSP')
                    ->icon('heroicon-o-building-library')
                    ->action(function (PayrollRun $record) {
                        GenerateBankFileJob::dispatch($record, 'bsp');
                        \Filament\Notifications\Notification::make()
                            ->title('BSP bank file queued')
                            ->success()->send();
                    }),

                Actions\Action::make('export_anz')
                    ->label('Bank File – ANZ')
                    ->icon('heroicon-o-building-library')
                    ->action(function (PayrollRun $record) {
                        GenerateBankFileJob::dispatch($record, 'anz');
                        \Filament\Notifications\Notification::make()
                            ->title('ANZ bank file queued')
                            ->success()->send();
                    }),

                Actions\Action::make('export_hfc')
                    ->label('Bank File – HFC')
                    ->icon('heroicon-o-building-library')
                    ->action(function (PayrollRun $record) {
                        GenerateBankFileJob::dispatch($record, 'hfc');
                        \Filament\Notifications\Notification::make()
                            ->title('HFC bank file queued')
                            ->success()->send();
                    }),

                Actions\Action::make('export_bred')
                    ->label('Bank File – BRED')
                    ->icon('heroicon-o-building-library')
                    ->action(function (PayrollRun $record) {
                        GenerateBankFileJob::dispatch($record, 'bred');
                        \Filament\Notifications\Notification::make()
                            ->title('BRED bank file queued')
                            ->success()->send();
                    }),

                Actions\Action::make('export_generic')
                    ->label('Bank File – Generic')
                    ->icon('heroicon-o-building-library')
                    ->action(function (PayrollRun $record) {
                        GenerateBankFileJob::dispatch($record, 'generic');
                        \Filament\Notifications\Notification::make()
                            ->title('Generic bank file queued')
                            ->success()->send();
                    }),
            ])->label('Exports')->icon('heroicon-o-arrow-down-tray'),

            Actions\EditAction::make(),
        ];
    }
}
