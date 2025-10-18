<?php

namespace App\Filament\Tenant\Resources\SellingResource\Pages;

use Filament\Forms\Get;
use Filament\Support\RawJs;
use Filament\Actions\Action;
use App\Models\Tenants\About;
use App\Models\Tenants\Product;
use App\Models\Tenants\Selling;
use App\Features\PrintSellingA5;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Tenant\Resources\SellingResource;
use App\Filament\Tenant\Resources\SellingDetailResource\RelationManagers\SellingDetailsRelationManager;
use App\Filament\Tenant\Resources\SellingResource\RelationManagers\ReturSellingsRelationManager;
use App\Services\Tenants\ReturnSellingService;
use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;
use Illuminate\Support\Number;

class ViewSelling extends ViewRecord
{
    protected static string $resource = SellingResource::class;

    public ?About $about = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->about = About::first();
    }

    public function getTitle(): string|Htmlable
    {
        return 'View '.$this->getRecord()->code;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make(__('Print invoice'))
                ->icon('heroicon-s-printer')
                ->extraAttributes([
                    'id' => 'printInvoice',
                ])
                ->color(Color::Teal)
                ->visible(can('can print selling') && feature(PrintSellingA5::class)),
            Action::make(__('Print receipt'))
                ->icon('heroicon-s-printer')
                ->extraAttributes([
                    'id' => 'printButton',
                ])
                ->visible(can('can print selling')),
            Action::make(__('Retur'))
                ->icon('heroicon-s-arrow-uturn-left')
                ->color(Color::Red)
                ->visible(function () {
                    $allowRetur = (!$this->record->paymentMethod->is_credit)
                        || ($this->record->paymentMethod->is_credit && $this->record->receivables->count() > 0 && $this->record->receivables->sum('rest_receivable') == 0);

                    return $allowRetur;
                })
                ->action(function (array $data, ReturnSellingService $returnSellingService) {
                    $sellingDetail = $this->record->sellingDetails()->where('id', $data['selling_detail_id'])->first();

                    if ($sellingDetail) {
                        $returnSellingService->processReturn($sellingDetail, $data);
                        $this->record->refresh();

                        Notification::make()
                            ->title(__('Retur processed successfully.'))
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title(__('Selling detail not found.'))
                            ->danger()
                            ->send();
                    }

                })
                ->form([
                    Grid::make()
                        ->schema([
                            Select::make('selling_detail_id')
                                ->label(__('Item'))
                                ->options($this->record->sellingDetails->pluck('product.name', 'id'))
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $oldItem = $get('selling_detail_id') ? $this->record->sellingDetails->where('id', $state)->first() : null;
                                    $newItem = $get('new_product_id') ? Product::find($get('new_product_id')) : null;
                                    $quantity = (int) $get('qty');
                                    $refundAmount = 0;
                                    $additionalAmount = 0;

                                    if ($newItem && $oldItem && $quantity > 0) {
                                        $oldAmount = ($oldItem->price - $oldItem->discount_price) * $quantity;
                                        $newAmount = $newItem->selling_price * $quantity;

                                        $additionalAmount = ($newAmount - $oldAmount);
                                        $refundAmount = 0;
                                    } else if ($newItem && !$oldItem) {
                                        $refundAmount = ($oldItem->price - $oldItem->discount_price) * $quantity;
                                        $additionalAmount = 0;
                                    }

                                    $set('additional_amount', $additionalAmount);
                                    $set('refund_amount', $refundAmount);
                                }),
                            Select::make('new_product_id')
                                ->label(__('Tukar dengan'))
                                ->searchable()
                                ->live()
                                ->getSearchResultsUsing(fn (string $search) => \App\Models\Tenants\Product::where('name', 'like', "%{$search}%")->limit(10)->pluck('name', 'id'))
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $oldItem = $get('selling_detail_id') ? $this->record->sellingDetails->where('id', $get('selling_detail_id'))->first() : null;
                                    $newItem = $get('new_item') ? Product::find($state) : null;
                                    $quantity = (int) $get('qty');
                                    $refundAmount = 0;
                                    $additionalAmount = 0;

                                    if ($newItem && $oldItem && $quantity > 0) {
                                        $oldAmount = ($oldItem->price - $oldItem->discount_price) * $quantity;
                                        $newAmount = $newItem->selling_price * $quantity;

                                        $additionalAmount = ($newAmount - $oldAmount);
                                        $refundAmount = 0;
                                    } else if ($newItem && !$oldItem) {
                                        $refundAmount = ($oldItem->price - $oldItem->discount_price) * $quantity;
                                        $additionalAmount = 0;
                                    }

                                    $set('additional_amount', $additionalAmount);
                                    $set('refund_amount', $refundAmount);
                                }),
                        ])
                        ->columns(2),
                    Grid::make()
                        ->schema([
                        TextInput::make('qty')
                            ->label(__('Qty'))
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $oldItem = $get('selling_detail_id') ? $this->record->sellingDetails->where('id', $get('selling_detail_id'))->first() : null;
                                $newItem = $get('new_product_id') ? Product::find($get('new_product_id')) : null;
                                $quantity = (int) $state;
                                $refundAmount = 0;
                                $additionalAmount = 0;

                                if ($newItem && $oldItem && $quantity > 0) {
                                    $oldAmount = ($oldItem->price - $oldItem->discount_price) * $quantity;
                                    $newAmount = $newItem->selling_price * $quantity;

                                    $additionalAmount = ($newAmount - $oldAmount);
                                    $refundAmount = 0;
                                } else if ($oldItem && !$newItem) {
                                    $refundAmount = ($oldItem->price - $oldItem->discount_price) * $quantity;
                                    $additionalAmount = 0;
                                }

                                $set('additional_amount', $additionalAmount);
                                $set('refund_amount', $refundAmount);
                            }),
                        Textarea::make('reason')
                            ->label(__('Reason'))
                            ->rows(3),
                    ])
                        ->columns(2),
                    Grid::make()
                        ->schema([
                            TextInput::make('refund_amount')
                                ->label(__('Jumlah yang dikembalikan'))
                                ->readOnly()
                                ->mask(RawJs::make('$money($input)'))
                                ->live(),
                            TextInput::make('additional_amount')
                                ->label(__('Jumlah yang harus dibayar'))
                                ->readOnly()
                                ->mask(RawJs::make('$money($input)'))
                                ->live(),
                        ])
                        ->columns(2),
                ])
                ->modalHeading(__('Retur Selling')),
        ];
    }

    public function getView(): string
    {
        return 'filament.tenant.resources.sellings.pages.view-selling';
    }

    public function getRecord(): Selling
    {
        return $this->record->load('sellingDetails.product');
    }

    public function getRelationManagers(): array
    {
        return [
            // SellingDetailsRelationManager::make(),
            ReturSellingsRelationManager::make(),
        ];
    }
}
