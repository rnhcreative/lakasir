<?php

namespace App\Filament\Tenant\Resources\SellingResource\Widgets;

use App\Models\Tenants\Profile;
use App\Models\Tenants\Selling;
use App\Models\Tenants\SellingDetail;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class SellingOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalRevenue = $this->getTotalRevenue();
        $todaySales = $this->getSalesToday();
        $discountToday = $this->getDiscountToday();

        return [
            can('read revenue overview') ? Stat::make(__('Today profit'), $totalRevenue['total_revenue'])
                ->descriptionIcon($totalRevenue['icon'])
                ->description($totalRevenue['description'])
                ->chart([$totalRevenue['yesterdayRevenue'], $totalRevenue['todayRevenue']])
                ->color($totalRevenue['color']) : null,
            can('read sales overview') ? Stat::make(__('Today transactions'), $todaySales) : null,
            can('read sales overview') ? Stat::make(__('Discount today'), $discountToday) : null,
        ];
    }

    private function getDiscountToday()
    {
        $carbon = now(config('setting.timezone'));
        $today = $carbon->startOfDay()->format('Y-m-d H:i:s e');
        $startDate = Carbon::parse($today)->setTimezone('UTC');
        $endDate = Carbon::parse($today)->setTimezone('UTC')->addDay();
        $totalDiscountSellings = Selling::whereBetween('date', [$startDate, $endDate])
            ->sum('discount_price');

        $totalDiscountSellingDetails = SellingDetail::whereHas('selling', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        })->sum('discount_price');

        $totalDiscount = $totalDiscountSellings + $totalDiscountSellingDetails;

        return Number::abbreviate($totalDiscount);
    }

    private function getSalesToday()
    {
        $carbon = now(config('setting.timezone'));
        $today = $carbon->startOfDay()->format('Y-m-d H:i:s e');
        $startDate = Carbon::parse($today)->setTimezone(config('setting.timezone') ?? 'UTC');

        $salesToday = Selling::whereDate('date', $startDate)->count();

        return $salesToday;
    }

    private function getTotalRevenue()
    {
        $carbon = now(config('setting.timezone'));
        $startOfDay = $carbon->startOfDay();
        $startOfYesterday = $startOfDay->copy()->subDay();

        $todayRevenue = $this->calculateRevenue($startOfDay, $startOfDay);
        $yesterdayRevenue = $this->calculateRevenue($startOfYesterday, $startOfDay);

        $totalYesterdayRevenue = $this->calculateTotalRevenue($yesterdayRevenue);
        $totalTodayRevenue = $this->calculateTotalRevenue($todayRevenue);

        $readable = $this->getReadableSuffix($totalTodayRevenue);

        $trendData = $this->getTrendData($totalYesterdayRevenue, $totalTodayRevenue);

        $percentage = $totalYesterdayRevenue ? (($totalTodayRevenue - $totalYesterdayRevenue) / $totalYesterdayRevenue) * 100 : 0;

        return [
            'total_revenue' => $readable,
            'description' => round($percentage).'% '.$trendData['trend'],
            'yesterdayRevenue' => $totalYesterdayRevenue,
            'todayRevenue' => $totalTodayRevenue,
            'color' => $trendData['color'],
            'icon' => $trendData['icon'],
        ];
    }

    private function calculateRevenue($start, $end)
    {
        return Selling::query()
            ->select(
                DB::raw('SUM(sellings.discount_price) as discount_price'),
                DB::raw('SUM(sellings.total_discount_per_item) as total_discount_per_item'),
                DB::raw('SUM(sellings.tax_price) as tax_price'),
                DB::raw('SUM(sellings.total_price) as total_price'),
                DB::raw('SUM(sellings.total_cost) as total_cost'),
            )
            ->isPaid()
            ->whereRaw("DATE(CONVERT_TZ(sellings.date, 'UTC', ?)) BETWEEN ? AND ?", [
                config('setting.timezone'),
                $start->format('Y-m-d'),
                $end->format('Y-m-d'),
            ])
            ->first();
    }

    private function calculateTotalRevenue($revenue)
    {
        $grossProfit = $revenue->total_price - $revenue->tax_price - $revenue->total_discount_per_item - $revenue->discount_price;

        return $grossProfit - $revenue->total_cost;
    }

    private function getReadableSuffix($totalRevenue)
    {
        return Number::currency($totalRevenue, locale: 'id', in: 'IDR');
    }

    private function getTrendData($totalYesterdayRevenue, $totalTodayRevenue)
    {
        if ($totalYesterdayRevenue > $totalTodayRevenue) {
            return [
                'trend' => __('decrease'),
                'color' => 'danger',
                'icon' => 'heroicon-m-arrow-trending-down',
            ];
        }

        if ($totalYesterdayRevenue < $totalTodayRevenue) {
            return [
                'trend' => __('increase'),
                'color' => 'success',
                'icon' => 'heroicon-m-arrow-trending-up',
            ];
        }

        return [
            'trend' => __('sideway'),
            'color' => 'warning',
            'icon' => 'heroicon-m-minus',
        ];
    }
}
