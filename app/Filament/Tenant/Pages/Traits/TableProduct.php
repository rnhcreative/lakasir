<?php

namespace App\Filament\Tenant\Pages\Traits;

use Closure;
use Filament\Tables\Table;
use App\Models\Tenants\Product;
use App\Models\Tenants\Setting;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Actions\HeaderActionsPosition;

trait TableProduct
{
    use InteractsWithTable;

    protected bool $tableProductHookRegistered = false;

    public function initializeTableProduct(): void
    {
        if ($this->tableProductHookRegistered) {
            return;
        }

        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_START,
            function () {
                return view('filament.hooks.product-category-filters', [
                    'resource' => $this,
                    'activeCategory' => $this->activeCategory,
                ]);
            }
        );

        $this->tableProductHookRegistered = true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // TODO: fix the query for product with this condition
                // * hide the prodcut when the type is product but that has a 0 stock
                // * show the product when the type is service but that has a 0 stock
                // * show the product when the type is procut but that has a 0 stock and then has a is_non_stock true
                Product::query()
                    ->where(function ($query) {
                        $query
                            ->where(function ($query) {
                                $query->where('stock', '>', 0);
                            });
                    })
                    ->where('show', true)
                    ->when(
                        $this->activeCategory,
                        fn ($q, $categoryId) => $q->where('category_id', $categoryId)
                    )
                    ->with(['stocks', 'CartItems'])
                    ->orderBy('name')
            )
            ->defaultPaginationPageOption(12)
            ->paginationPageOptions([12, 24, 36, 48])
            ->columns([
                Stack::make([
                    ImageColumn::make('hero_image_url')
                        ->translateLabel()
                        ->alignCenter()
                        ->extraAttributes([
                            'class' => 'py-0',
                        ])
                        ->extraImgAttributes([
                            'class' => 'mb-4 object-cover -mt-4 xl:w-[200px] md:w-[180px] w-[150px]',
                        ])
                        ->hidden(fn () => ! $this->showProductImage)
                        ->height(100),
                    TextColumn::make('selling_price')
                        ->color('primary')
                        ->money(config('setting.currency'))
                        ->columnStart(0),
                    TextColumn::make('name')
                        ->size('lg')
                        ->searchable(['sku', 'name', 'barcode'])
                        ->extraAttributes([
                            'class' => 'font-bold',
                        ]),
                    TextColumn::make('stock')
                        ->hidden(function (Product $product) {
                            return $product->is_non_stock;
                        })
                        ->icon(function (Product $product) {
                            if ($product->is_non_stock) {
                                return '';
                            }

                            return $product->stock < 10
                                    ? 'heroicon-s-information-circle'
                                : '';
                        })
                        ->iconColor('danger')
                        ->extraAttributes([
                            'class' => 'font-bold',
                        ])
                        ->formatStateUsing(fn (Product $product) => __('Stock').': '.$product->stocks->sum('stock')),
                ]),
            ])
            ->contentGrid([
                'md' => 3,
                'xl' => 4,
            ])
            ->headerActionsPosition(HeaderActionsPosition::Bottom)
            ->searchPlaceholder(__('Search (SKU, name, barcode)'))
            ->actions([
                Action::make('insert_amount')
                    ->translateLabel()
                    ->icon('heroicon-o-plus')
                    ->button()
                    ->form([
                        TextInput::make('amount')
                            ->translateLabel()
                            ->extraAttributes([
                                'focus',
                            ])
                            ->rules([
                                function (Product $product) {
                                    return function (string $attribute, $value, Closure $fail) use ($product) {
                                        if (! $this->validateStock($product, $value)) {
                                            $fail('Stock is out');
                                        }
                                    };
                                },
                            ])
                            ->default(1),
                    ])
                    ->extraAttributes([
                        'class' => 'mr-auto',
                    ])
                    ->action(fn (Product $product, array $data) => $this->addCart($product, $data))
                    ->hiddenLabel(),
                Action::make('cart')
                    ->label(function (Product $product) {
                        return $product->CartItems->first()?->qty ?? '';
                    })
                    ->color('white')
                    ->icon('heroicon-o-shopping-bag')
                    ->hidden(fn (Product $product) => ! $product->CartItems),
                ]);
    }
}
