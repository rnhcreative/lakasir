<?php

namespace App\Filament\Tenant\Resources\ReceivableResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReceivablePaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'receivablePayments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->label(__('Payment Method')),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Cashier')),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->label(__('Date')),
                Tables\Columns\TextColumn::make('amount')
                    ->money(config('settings.currency', 'idr'), true)
                    ->label(__('Amount')),
            ])
            ->heading('Pembayaran Utang')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['paymentMethod']));
    }
}
