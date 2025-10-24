<?php

namespace App\Filament\Tenant\Resources\ExpenseResource\Pages;

use App\Filament\Tenant\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return '/member/expenses';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['expense_date'] = \Carbon\Carbon::parse($data['expense_date'])
            ->setTimeFrom(\Carbon\Carbon::now());

        return $data;
    }
}
