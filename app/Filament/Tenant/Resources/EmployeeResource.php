<?php

namespace App\Filament\Tenant\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Tenants\Employee;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Traits\HasTranslatableResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Tenant\Resources\EmployeeResource\Pages;
use App\Filament\Tenant\Resources\EmployeeResource\RelationManagers;
use App\Filament\Tenant\Resources\EmployeeResource\RelationManagers\SellingsRelationManager;
use Filament\Resources\RelationManagers\RelationManager;

class EmployeeResource extends Resource
{
    use HasTranslatableResource;

    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('Contact'))
                    ->unique(ignoreRecord: true)
                    ->placeholder(__('Please provide a valid email address or whatsapp/phone number.')),
                DatePicker::make('joined_date')
                    ->translateLabel()
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
                ->defaultSort('created_at', 'desc')
                ->columns([
                    TextColumn::make('name')
                        ->label(__('Name'))
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('email')
                        ->label(__('Contact'))
                        ->searchable(),
                ])
                ->filters([
                    //
                ])
                ->actions([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ])
                ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\DeleteBulkAction::make(),
                    ]),
                ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('name')->translateLabel(),
                TextEntry::make('email')->label(__('Contact')),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SellingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
