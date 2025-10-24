<?php

namespace App\Filament\Tenant\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use App\Models\Tenants\Expense;
use App\Models\Tenants\Profile;
use App\Models\Tenants\Setting;
use Filament\Resources\Resource;
use App\Models\Tenants\ExpenseType;
use Filament\Forms\Components\Radio;
use App\Models\Tenants\PaymentMethod;
use App\Traits\HasTranslatableResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Tenant\Resources\ExpenseResource\Pages;
use App\Filament\Tenant\Resources\ExpenseResource\RelationManagers;

class ExpenseResource extends Resource
{
    use HasTranslatableResource;

    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('expense_type_id')
                    ->label(__('Expense Type'))
                    ->options(ExpenseType::pluck('name', 'id'))
                    ->native(false)
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->translateLabel()
                            ->required(),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        $type = new ExpenseType();
                        $type->fill($data);
                        $type->save();

                        return $type->getKey();
                    })
                    ->required(),
                Radio::make('payment_method_id')
                    ->label(__('Payment Method'))
                    ->options(PaymentMethod::where('is_credit', false)->pluck('name', 'id'))
                    ->inline()
                    ->inlineLabel(false),
                Forms\Components\TextInput::make('amount')
                    ->label(__('Amount'))
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->numeric()
                    ->required(),
                Forms\Components\DatePicker::make('expense_date')
                    ->required()
                    ->label(__('Date'))
                    ->default(now()),

                Forms\Components\Textarea::make('note')
                    ->label(__('Note'))
                    ->rows(3)
                    ->columnSpanFull()
                    ->translateLabel(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_date')
                    ->label(__('Date'))
                    ->date(timezone: config('setting.timezone'))
                    ->sortable(),
                TextColumn::make('expenseType.name')
                    ->label(__('Type'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->money(config('setting.currency'))
                    ->sortable(),
                TextColumn::make('note')
                    ->label(__('Note'))
                    ->limit(50)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('paymentMethod.name')
                    ->label(__('Payment Method'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
