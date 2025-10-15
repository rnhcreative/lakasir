<?php

namespace App\Filament\Tenant\Resources\ReceivableResource\Widgets;

use App\Models\Tenants\Receivable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class StatsReceivable extends BaseWidget
{
    protected function getStats(): array
    {
        $receivable = Receivable::query()
            ->select(
                DB::raw('SUM(total_receivable) as total_receivable'),
                DB::raw('SUM(total_receivable - rest_receivable) as total_paid'),
                DB::raw('SUM(rest_receivable) as rest_receivable')
            )
            ->first();

        return [
            Stat::make(__('Total Receivable'), Number::currency($receivable->total_receivable ?? 0, in: 'IDR', locale: 'id', precision: 0)),
            Stat::make(__('Total Paid'), Number::currency($receivable->total_paid ?? 0, in: 'IDR', locale: 'id', precision: 0)),
            Stat::make(__('Total Unpaid'), Number::currency($receivable->rest_receivable ?? 0, in: 'IDR', locale: 'id', precision: 0)),
        ];
    }
}
