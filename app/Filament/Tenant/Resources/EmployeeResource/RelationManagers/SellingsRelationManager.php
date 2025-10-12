<?php

namespace App\Filament\Tenant\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Tenants\Profile;
use App\Models\Tenants\Setting;
use App\Features\ProductInitialPrice;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class SellingsRelationManager extends RelationManager
{
    protected static string $relationship = 'sellings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('code')->translateLabel()
                    ->searchable(),
                TextColumn::make('member.name')->translateLabel()
                    ->searchable(),
                TextColumn::make('date')
                    ->dateTime(timezone: Profile::get()->timezone)
                    ->translateLabel()
                    ->sortable(),
                TextColumn::make('total_price')
                    ->label('Sub Total')
                    ->translateLabel()
                    ->sortable()
                    ->money(Setting::get('currency', 'IDR')),
                TextColumn::make('discount_price')
                    ->label('Discount')
                    ->translateLabel()
                    ->money(Setting::get('currency', 'IDR')),
                TextColumn::make('tax_price')
                    ->label('Tax')
                    ->translateLabel()
                    ->sortable()
                    ->visible(feature(ProductInitialPrice::class))
                    ->money(Setting::get('currency', 'IDR')),
                TextColumn::make('grand_total_price')
                    ->label('Total')
                    ->translateLabel()
                    ->sortable()
                    ->money(Setting::get('currency', 'IDR')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([

            ])
            ->bulkActions([

            ])
            ->heading(__('Selling'))
            ->searchPlaceholder('Kode, Pelanggan')
            ->recordUrl(fn (Model $record): string => route('filament.tenant.resources.sellings.view', $record));
    }
}
