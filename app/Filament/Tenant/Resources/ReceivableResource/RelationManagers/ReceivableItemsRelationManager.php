<?php

namespace App\Filament\Tenant\Resources\ReceivableResource\RelationManagers;

use App\Models\Tenants\Setting;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReceivableItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'receivableItems';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->translateLabel(),
                Tables\Columns\TextColumn::make('amount')
                    ->translateLabel(),
                Tables\Columns\TextColumn::make('price')
                    ->translateLabel()
                    ->money(config('setting.currency')),
                Tables\Columns\TextColumn::make('subtotal')
                    ->translateLabel()
                    ->money(config('setting.currency')),
            ])
            ->filters([
                //
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
