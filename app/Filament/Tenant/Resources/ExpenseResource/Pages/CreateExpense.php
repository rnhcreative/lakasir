<?php

namespace App\Filament\Tenant\Resources\ExpenseResource\Pages;

use App\Filament\Tenant\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getRedirectUrl(): string
    {
        return '/member/expenses';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['expense_date'] = \Carbon\Carbon::parse($data['expense_date'])
            ->setTimeFrom(\Carbon\Carbon::now());

        return $data;
    }
}
