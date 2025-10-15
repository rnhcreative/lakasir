<?php

namespace App\Filament\Tenant\Resources\SellingDetailResource\RelationManagers;

use App\Features\ProductInitialPrice;
use App\Models\Tenants\SellingDetail;
use App\Models\Tenants\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;

class SellingDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'sellingDetails';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('product_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->translateLabel(),
                Tables\Columns\TextColumn::make('qty')
                    ->translateLabel(),
                Tables\Columns\TextColumn::make('price')
                    ->translateLabel()
                    ->sortable()
                    ->money(config('setting.currency')),
                Tables\Columns\TextColumn::make('discount')
                    ->getStateUsing(fn (SellingDetail $sellingDetail) => Number::currency(
                        $sellingDetail->discount_price, config('setting.currency'))
                    )
                    ->translateLabel()
                    ->sortable()
                    ->money(config('setting.currency')),
                Tables\Columns\TextColumn::make('discount_price')
                    ->getStateUsing(fn (SellingDetail $sellingDetail) => Number::currency(
                        $sellingDetail->price - $sellingDetail->discount_price, config('setting.currency'))
                    )
                    ->translateLabel()
                    ->sortable()
                    ->money(config('setting.currency')),
                Tables\Columns\TextColumn::make('cost')
                    ->translateLabel()
                    ->visible(feature(ProductInitialPrice::class))
                    ->sortable()
                    ->money(config('setting.currency')),
            ])
            ->paginated(false);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Items');
    }
}
