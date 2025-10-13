<?php

namespace App\Filament\Tenant\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Laravel\Pennant\Feature;
use App\Features\ProductStock;
use App\Models\Tenants\Profile;
use App\Models\Tenants\Setting;
use App\Models\Tenants\PriceUnit;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;

class PriceUnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'priceUnits';

    public array $unitOptions = [];

    public function mount(): void
    {
        $unitOptions = PriceUnit::pluck('unit', 'unit')->toArray();

        // make units is uppercase and only show unique units
        $unitOptions = array_map(function ($unit) {
            return strtoupper($unit);
        }, $unitOptions);

        // Only show unique units
        $unitOptions = array_combine($unitOptions, $unitOptions);

        foreach ($unitOptions as $key => $value) {
            $this->unitOptions[$value] = $value;
        }
    }

    public function form(Form $form): Form
    {
        $self = $this;

        return $form
            ->columns(1)
            ->schema([
                Select::make('unit')
                    ->label(__('Unit Name'))
                    ->translateLabel()
                    ->options($self->unitOptions)
                    ->createOptionForm([
                        Forms\Components\TextInput::make('label')
                            ->label(__('Unit Name'))
                            ->translateLabel()
                            ->required(),
                    ])
                    ->createOptionUsing(function (array $data) {
                        // tambahkan opsi ke array lokal
                        $this->unitOptions[$data['label']] = $data['label'];

                        // kembalikan nilai yang akan dipilih otomatis setelah create
                        return $data['label'];
                    })
                    ->createOptionAction(fn (Forms\Components\Actions\Action $action)
                        => $action
                            ->modalWidth('md')
                    )
                    ->searchable()
                    ->reactive()
                    ->required(),
                Forms\Components\TextInput::make('selling_price')
                    ->translateLabel()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->numeric()
                    ->prefix(Setting::get('currency', 'IDR'))
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('selling_price')
            ->columns([
                Tables\Columns\TextColumn::make('unit')
                    ->label(__('Unit Name'))
                    ->translateLabel(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->translateLabel()
                    ->money(
                        currency: Setting::get('currency', 'IDR'),
                        locale: Profile::get()->locale ?? config('app.locale')
                    ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Price Units');
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
