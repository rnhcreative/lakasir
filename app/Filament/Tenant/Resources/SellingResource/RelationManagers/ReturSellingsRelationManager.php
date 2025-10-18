<?php

namespace App\Filament\Tenant\Resources\SellingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReturSellingsRelationManager extends RelationManager
{
    protected static string $relationship = 'returSellings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->date(timezone: config('setting.timezone'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('sellingDetail.product.name')
                    ->label(__('Item')),
                Tables\Columns\TextColumn::make('newProduct.name')
                    ->label(__('Item yang ditukar')),
                Tables\Columns\TextColumn::make('qty')
                    ->translateLabel(),
                Tables\Columns\TextColumn::make('refund_amount')
                    ->translateLabel()
                    ->money(config('setting.currency')),
                Tables\Columns\TextColumn::make('additional_amount')
                    ->translateLabel()
                    ->money(config('setting.currency')),
                Tables\Columns\TextColumn::make('reason')
                    ->translateLabel()
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([

            ])
            ->bulkActions([

            ])
            ->heading(__('Retur Items'));
    }
}
