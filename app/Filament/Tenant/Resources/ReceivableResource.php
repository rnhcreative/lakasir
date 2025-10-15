<?php

namespace App\Filament\Tenant\Resources;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Tenants\Member;
use App\Models\Tenants\Setting;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use App\Traits\HasTranslatableResource;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Infolists\Components\TextEntry;
use App\Services\Tenants\ReceivablePaymentService;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Filament\Tenant\Resources\ReceivableResource\Pages;
use App\Filament\Tenant\Resources\ReceivableResource\Traits\HasReceivablePaymentForm;
use App\Filament\Tenant\Resources\ReceivableResource\RelationManagers\ReceivablesRelationManager;
use App\Filament\Tenant\Resources\ReceivableResource\RelationManagers\ReceivablePaymentsRelationManager;

class ReceivableResource extends Resource
{
    use HasReceivablePaymentForm, HasTranslatableResource;

    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function getModelLabel(): string
    {
        return __('Receivable');
    }

    public static function getLabel(): string
    {
        return __('Receivable');
    }

    public static function table(Table $table): Table
    {
        $self = new self();

        $query = Member::query()
            ->with('receivables')
            ->whereHas('receivables');

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('name')
                    ->translateLabel()
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Contact'))
                    ->searchable(),
                TextColumn::make('receivables_sum_total_receivable')
                    ->sum('receivables', 'total_receivable')
                    ->label(__('Total Debt'))
                    ->money(Setting::get('currency', 'IDR')),
                TextColumn::make('receivables_sum_total_paid')
                    ->getStateUsing(fn (Model $record): float => $record->receivables()->sum('total_receivable') - $record->receivables()->sum('rest_receivable'))
                    ->label(__('Total Paid'))
                    ->money(Setting::get('currency', 'IDR'))
                    ->translateLabel(),
                TextColumn::make('receivables_sum_rest_receivable')
                    ->sum('receivables', 'rest_receivable')
                    ->label(__('Rest Debt'))
                    ->money(Setting::get('currency', 'IDR'))
                    ->translateLabel(),
                TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(function (Model $record) {
                        return ($record->receivables->sum('rest_receivable')) == 0 ? __('Paid off') : __('Unpaid');
                    })
                    ->iconColor(fn (string $state): string => match ($state) {
                        __('Unpaid') => 'danger',
                        __('Paid off') => 'success',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        __('Paid off') => 'heroicon-o-check-circle',
                        __('Unpaid') => 'heroicon-o-exclamation-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        __('Unpaid') => 'danger',
                        __('Paid off') => 'success',
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('add_payment')
                    ->translateLabel()
                    ->icon('heroicon-s-credit-card')
                    ->model(Member::class)
                    ->visible(function ($record) {
                        if ($record->receivables->sum('rest_receivable') > 0 && can('create receivable payment')) {
                            return true;
                        }

                        return false;
                    })
                    ->form(fn ($record) => $self->getFormPaymentByMember($record))
                    ->action(function (array $data, Member $member, ReceivablePaymentService $dpService, $livewire): void {
                        $dpService->createByMember($member, $data);

                        // Show success notification
                        Notification::make()
                            ->title('Pembayaran utang berhasil ditambahkan')
                            ->body('Data pembayaran baru telah disimpan.')
                            ->success()
                            ->send();

                        // Force Livewire to re-render this page
                        $livewire->dispatch('$refresh', bubbles: true);
                    }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('name')
                ->translateLabel(),
            TextEntry::make('email')
                ->label(__('Contact'))
                ->translateLabel(),
            TextEntry::make('total_receivable')
                ->getStateUsing(fn (Model $record): float => $record->receivables()->sum('total_receivable'))
                ->label(__('Total Debt'))
                ->money(Setting::get('currency', 'IDR')),
            TextEntry::make('rest_receivable')
                ->getStateUsing(fn (Model $record): float => $record->receivables()->sum('rest_receivable'))
                ->label(__('Rest Debt'))
                ->money(Setting::get('currency', 'IDR')),
            TextEntry::make('total_paid')
                ->getStateUsing(fn (Model $record): float => $record->receivables()->sum('total_receivable') - $record->receivables()->sum('rest_receivable'))
                ->label(__('Total Paid'))
                ->money(Setting::get('currency', 'IDR')),
            TextEntry::make('status')
                ->label(__('Status'))
                ->getStateUsing(function (Model $record) {
                    return ($record->receivables->sum('rest_receivable')) == 0 ? __('Paid off') : __('Unpaid');
                })
                ->color(fn (string $state): string => match ($state) {
                    __('Unpaid') => 'danger',
                    __('Paid off') => 'success',
                })
                ->badge()
                ->iconColor(fn (string $state): string => match ($state) {
                    __('Unpaid') => 'danger',
                    __('Paid off') => 'success',
                })
                ->icon(fn (string $state): string => match ($state) {
                    __('Paid off') => 'heroicon-o-check-circle',
                    __('Unpaid') => 'heroicon-o-exclamation-circle',
                }),
        ]);

    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('', [
                ReceivablesRelationManager::make(),
                ReceivablePaymentsRelationManager::make(),
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReceivables::route('/'),
            'view' => Pages\ViewReceivable::route('/{record}'),
        ];
    }
}
