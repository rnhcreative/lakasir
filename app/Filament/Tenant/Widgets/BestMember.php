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
                            $query->whereDate('date', today()->toDateString());
                        } elseif ($value === 'this_week') {
                            $query->whereBetween('date', [today()->startOfWeek()->toDateString(), today()->endOfWeek()->toDateString()]);
                        } elseif ($value === 'last_week') {
                            $query->whereBetween('date', [today()->subWeek()->startOfWeek()->toDateString(), today()->subWeek()->endOfWeek()->toDateString()]);
                        } elseif ($value === 'this_month') {
                            $query->whereBetween('date', [today()->startOfMonth()->toDateString(), today()->endOfMonth()->toDateString()]);
                        } elseif ($value === 'last_month') {
                            $query->whereBetween('date', [today()->subMonth()->startOfMonth()->toDateString(), today()->subMonth()->endOfMonth()->toDateString()]);
                        } elseif ($value === 'this_year') {
                            $query->whereBetween('date', [today()->startOfYear()->toDateString(), today()->endOfYear()->toDateString()]);
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
