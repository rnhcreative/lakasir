<?php

namespace App\Filament\Tenant\Widgets;

use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use App\Models\Tenants\SellingDetail;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class BestSellingProduct extends BaseWidget
{
    public function table(Table $table): Table
    {
        $bestSellingProduct = SellingDetail::query()
            ->select(
                '*',
                DB::raw('SUM(qty) as total_qty')
            )
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
                                $query->whereRaw("DATE(CONVERT_TZ(date, 'UTC', ?) = ?)", [
                                    config('app.timezone'),
                                    today(config('app.timezone'))->format('Y-m-d'),
                                ]);
                            });
                        } elseif ($value === 'this_week') {
                            $query->whereHas('selling', function ($query) {
                                $startDate = today(config('app.timezone'))->startOfWeek();
                                $endDate = today(config('app.timezone'))->endOfWeek();

                                $query->whereRaw("DATE(CONVERT_TZ(date, 'UTC', ?)) BETWEEN ? AND ?", [
                                    config('app.timezone'),
                                    $startDate->format('Y-m-d'),
                                    $endDate->format('Y-m-d'),
                                ]);
                            });
                        } elseif ($value === 'last_week') {
                            $query->whereHas('selling', function ($query) {
                                $startDate = today(config('app.timezone'))->subWeek()->startOfWeek();
                                $endDate = today(config('app.timezone'))->subWeek()->endOfWeek();

                                $query->whereRaw("DATE(CONVERT_TZ(date, 'UTC', ?)) BETWEEN ? AND ?", [
                                    config('app.timezone'),
                                    $startDate->format('Y-m-d'),
                                    $endDate->format('Y-m-d'),
                                ]);
                            });
                        } elseif ($value === 'this_month') {
                            $query->whereHas('selling', function ($query) {
                                $startDate = today(config('app.timezone'))->startOfMonth();
                                $endDate = today(config('app.timezone'))->endOfMonth();

                                $query->whereRaw("DATE(CONVERT_TZ(date, 'UTC', ?)) BETWEEN ? AND ?", [
                                    config('app.timezone'),
                                    $startDate->format('Y-m-d'),
                                    $endDate->format('Y-m-d'),
                                ]);
                            });
                        } elseif ($value === 'last_month') {
                            $query->whereHas('selling', function ($query) {
                                $startDate = today(config('app.timezone'))->subMonth()->startOfMonth();
                                $endDate = today(config('app.timezone'))->subMonth()->endOfMonth();

                                $query->whereRaw("DATE(CONVERT_TZ(date, 'UTC', ?)) BETWEEN ? AND ?", [
                                    config('app.timezone'),
                                    $startDate->format('Y-m-d'),
                                    $endDate->format('Y-m-d'),
                                ]);
                            });
                        } elseif ($value === 'this_year') {
                            $query->whereHas('selling', function ($query) {
                                $startDate = today(config('app.timezone'))->startOfYear();
                                $endDate = today(config('app.timezone'))->endOfYear();

                                $query->whereRaw("DATE(CONVERT_TZ(date, 'UTC', ?)) BETWEEN ? AND ?", [
                                    config('app.timezone'),
                                    $startDate->format('Y-m-d'),
                                    $endDate->format('Y-m-d'),
                                ]);
                            });
                        }
                    })
                    ->selectablePlaceholder(false),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->hiddenFilterIndicators()
            ->heading(__('Top Products'))
            ->recordUrl(
                fn (Model $record): string => route('filament.tenant.resources.products.view', ['record' => $record]),
            )
            ->paginated(false);
    }
}
