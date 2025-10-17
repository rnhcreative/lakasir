<?php

namespace App\Filament\Tenant\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Tenants\Member;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use App\Traits\HasTranslatableResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Tenant\Resources\MemberResource\Pages;
use App\Filament\Tenant\Resources\MemberResource\RelationManagers\SellingsRelationManager;

class MemberResource extends Resource
{
    use HasTranslatableResource;

    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('identity_type')
                    ->translateLabel()
                    ->options([
                        'sim' => 'Sim',
                        'ktp' => 'Ktp',
                        'other' => __('Other'),
                    ]),
                TextInput::make('identity_number')
                    ->label(__('Identity number'))
                    ->required(),
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required(),
                TextInput::make('code')
                    ->label(__('Code'))
                    ->unique(ignoreRecord: true),
                TextInput::make('address')
                    ->label(__('Address')),
                TextInput::make('email')
                    ->label(__('Contact'))
                    ->unique(ignoreRecord: true)
                    ->placeholder(__('Please provide a valid email address or whatsapp/phone number.')),
                DatePicker::make('joined_date')
                    ->translateLabel(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label(__('Code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address')
                    ->label(__('Address'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Contact'))
                    ->searchable(),

                TextColumn::make('identity_number')
                    ->label(__('Identity number'))
                    ->searchable()
                    ->sortable(),
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
                TextEntry::make('code')->translateLabel(),
                TextEntry::make('identity_number')->translateLabel(),
                TextEntry::make('email')->label(__('Contact')),
                TextEntry::make('address')->translateLabel(),
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
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'view' => Pages\ViewMember::route('/{record}'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
