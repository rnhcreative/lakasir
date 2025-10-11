<?php

namespace App\Filament\Tenant\Widgets;

use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use App\Models\Tenants\SellingDetail;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Widgets\TableWidget as BaseWidget;

class BestSellingProduct extends BaseWidget
{
    protected function getTableHeading(): string|Htmlable|null
    {
        return __('Top Products');
    }

    public function table(Table $table): Table
    {
        $startDate = today()->startOfDay();
        $endDate = today()->endOfDay();

        $bestSellingProduct = SellingDetail::query()
            ->select(
                '*',
                DB::raw('SUM(qty) as total_qty')
            )
            ->whereHas('selling', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->limit(5)
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->with('product', 'selling');

        return $table
            ->query(
                $bestSellingProduct
            )
            ->columns([
                TextColumn::make('product.name')
                    ->translateLabel(),
                TextColumn::make('total_qty')
                    ->translateLabel(),
            ])
            ->filters([
                SelectFilter::make('period')
                    ->label(false)
                    ->options([
                        'today' => __('Today'),
                        'this_week' => __('This Week'),
                        'last_week' => __('Last Week'),
                        'this_month' => __('This Month'),
                        'last_month' => __('Last Month'),
                        'this_year' => __('This Year'),
                    ])
                    ->default('today')
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? 'today';
                        $now = now();
                        if ($value === 'today') {
                            $query->whereHas('selling', function ($query) {
                                $query->whereDate('date', today()->toDateString());
                            });
                        } elseif ($value === 'this_week') {
                            $query->whereHas('selling', function ($query) {
                                $query->whereBetween('date', [today()->startOfWeek()->toDateString(), today()->endOfWeek()->toDateString()]);
                            });
                        } elseif ($value === 'last_week') {
                            $query->whereHas('selling', function ($query) {
                                $query->whereBetween('date', [today()->subWeek()->startOfWeek()->toDateString(), today()->subWeek()->endOfWeek()->toDateString()]);
                            });
                        } elseif ($value === 'this_month') {
                            $query->whereHas('selling', function ($query) {
                                $query->whereBetween('date', [today()->startOfMonth()->toDateString(), today()->endOfMonth()->toDateString()]);
                            });
                        } elseif ($value === 'last_month') {
                            $query->whereHas('selling', function ($query) {
                                $query->whereBetween('date', [today()->subMonth()->startOfMonth()->toDateString(), today()->subMonth()->endOfMonth()->toDateString()]);
                            });
                        } elseif ($value === 'this_year') {
                            $query->whereHas('selling', function ($query) {
                                $query->whereBetween('date', [today()->startOfYear()->toDateString(), today()->endOfYear()->toDateString()]);
                            });
                        }
                    })
                    ->selectablePlaceholder(false),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->hiddenFilterIndicators()
            ->paginated(false);
    }
}
