<?php

namespace App\Filament\Tenant\Widgets;

use Filament\Tables\Table;
use App\Models\Tenants\Product;
use App\Models\Tenants\Selling;
use App\Models\Tenants\Setting;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\TableWidget as BaseWidget;

class BestMember extends BaseWidget
{
    public function table(Table $table): Table
    {
        $bestMember = Selling::query()
            ->select(
                '*',
                DB::raw('SUM(total_price) as total_purchasing')
            )
            ->whereNotNull('member_id')
            ->limit(5)
            ->groupBy('member_id')
            ->orderBy(DB::raw('SUM(total_price)'), 'desc')
            ->with('member');

        return $table
            ->query(
                $bestMember
            )
            ->columns([
                TextColumn::make('member.name')
                    ->label(__('Name')),
                TextColumn::make('member.email')
                    ->label(__('Contact')),
                TextColumn::make('total_purchasing')
                    ->translateLabel()
                    ->money(config('setting.currency')),
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
                        if ($value === 'today') {
                            $query->whereRaw("DATE(CONVERT_TZ(date, 'UTC', ?) = ?)", [
                                config('app.timezone'),
                                today(config('app.timezone'))->format('Y-m-d'),
                            ]);
                        } elseif ($value === 'this_week') {
                            $startDate = today(config('app.timezone'))->startOfWeek();
                            $endDate = today(config('app.timezone'))->endOfWeek();

                            $query->whereRaw("DATE(CONVERT_TZ(date, 'UTC', ?)) BETWEEN ? AND ?", [
                                config('app.timezone'),
                                $startDate->format('Y-m-d'),
                                $endDate->format('Y-m-d'),
                            ]);
                        } elseif ($value === 'last_week') {
                            $startDate = today(config('app.timezone'))->subWeek()->startOfWeek();
                            $endDate = today(config('app.timezone'))->subWeek()->endOfWeek();

                            $query->whereRaw("DATE(CONVERT_TZ(date, 'UTC', ?)) BETWEEN ? AND ?", [
                                config('app.timezone'),
                                $startDate->format('Y-m-d'),
                                $endDate->format('Y-m-d'),
                            ]);
                        } elseif ($value === 'this_month') {
                            $startDate = today(config('app.timezone'))->startOfMonth();
                            $endDate = today(config('app.timezone'))->endOfMonth();

                            $query->whereRaw("DATE(CONVERT_TZ(date, 'UTC', ?)) BETWEEN ? AND ?", [
                                config('app.timezone'),
                                $startDate->format('Y-m-d'),
                                $endDate->format('Y-m-d'),
                            ]);
                        } elseif ($value === 'last_month') {
                            $startDate = today(config('app.timezone'))->subMonth()->startOfMonth();
                            $endDate = today(config('app.timezone'))->subMonth()->endOfMonth();

                            $query->whereRaw("DATE(CONVERT_TZ(date, 'UTC', ?)) BETWEEN ? AND ?", [
                                config('app.timezone'),
                                $startDate->format('Y-m-d'),
                                $endDate->format('Y-m-d'),
                            ]);
                        } elseif ($value === 'this_year') {
                            $startDate = today(config('app.timezone'))->startOfYear();
                            $endDate = today(config('app.timezone'))->endOfYear();

                            $query->whereRaw("DATE(CONVERT_TZ(date, 'UTC', ?)) BETWEEN ? AND ?", [
                                config('app.timezone'),
                                $startDate->format('Y-m-d'),
                                $endDate->format('Y-m-d'),
                            ]);
                        }
                    })
                    ->selectablePlaceholder(false),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->hiddenFilterIndicators()
            ->heading(__('Top Member'))
            ->recordUrl(
                fn (Model $record): string => route('filament.tenant.resources.members.edit', ['record' => $record]),
            )
            ->paginated(false);
    }
}
