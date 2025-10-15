<?php

namespace App\Filament\Tenant\Resources\ReceivableResource\RelationManagers;

use Filament\Tables\Table;
use App\Models\Tenants\Profile;
use App\Models\Tenants\Receivable;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenants\ReceivablePayment;
use Illuminate\Database\Eloquent\Builder;
use App\Services\Tenants\ReceivablePaymentService;
use App\Filament\Tenant\Resources\Traits\RefreshThePage;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Tenant\Resources\ReceivableResource\Traits\HasReceivablePaymentForm;

class ReceivablesRelationManager extends RelationManager
{
    use HasReceivablePaymentForm, RefreshThePage;

    protected static string $relationship = 'receivables';

    public function table(Table $table): Table
    {
        $self = $this;

        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('selling.date')
                    ->label(__('Date'))
                    ->dateTime(timezone: config('setting.timezone'))
                    ->translateLabel(),
                TextColumn::make('selling.code')
                    ->translateLabel()
                    ->label(__('Selling Code'))
                    ->url(fn (Model $record): ?string => route('filament.tenant.resources.sellings.view', $record->selling)),
                TextColumn::make('total_receivable')
                    ->translateLabel()
                    ->label(__('Total Receivable'))
                    ->money(config('settings.currency', 'IDR')),
                TextColumn::make('total_paid')
                    ->translateLabel()
                    ->label(__('Total Paid'))
                    ->getStateUsing(fn (Model $record): float => $record->total_receivable - $record->rest_receivable)
                    ->money(config('settings.currency', 'IDR')),
                TextColumn::make('rest_receivable')
                    ->translateLabel()
                    ->label(__('Total Unpaid'))
                    ->money(config('settings.currency', 'IDR')),
            ])
            ->actions([
                Action::make('add_payment')
                    ->translateLabel()
                    ->icon('heroicon-s-credit-card')
                    ->model(ReceivablePayment::class)
                    ->visible(function ($record) {
                        if (! $record->status && can('create receivable payment')) {
                            return true;
                        }

                        return false;
                    })
                    ->form(fn ($record) => $self->getFormPayment($record))
                    ->action(function (array $data, Receivable $receivable, ReceivablePaymentService $dpService): void {
                        $dpService->create($receivable, $data);
                    })
                    ->visible(fn (Model $record): bool => $record->rest_receivable > 0),
            ])
            ->heading('Transaksi')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('selling'));
    }
}
