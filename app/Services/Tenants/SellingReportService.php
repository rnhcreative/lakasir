<?php

namespace App\Services\Tenants;

use App\Models\Tenants\About;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use App\Models\Tenants\Selling;
use Illuminate\Support\Facades\DB;
use App\Models\Tenants\SellingDetail;
use Illuminate\Database\Eloquent\Builder;

class SellingReportService
{
    public function generate(array $data)
    {
        $timezone = config('setting.timezone');
        $about = About::first();
        $startDate = Carbon::parse($data['start_date'], $timezone)->setTimezone('UTC');
        $endDate = Carbon::parse($data['end_date'], $timezone)->addDay()->setTimezone('UTC');

        $sellings = Selling::query()
            ->select(
                'id',
                'date',
                DB::raw("SUM(total_price) as total_selling"),
                DB::raw("COUNT(id) as total_transaction"),
                DB::raw("SUM(total_qty) as total_item"),
                DB::raw("SUM(discount_price) as total_discount"),
                DB::raw("SUM(total_price - total_cost) as total_profit"),
            )
            ->when($data['start_date'] && $data['end_date'], function (Builder $query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->with('sellingDetails')
            ->orderBy('created_at', 'asc')
            ->groupBy(DB::raw('DATE(date)'))
            ->get();

        $header = [
            'shop_name' => $about?->shop_name,
            'shop_location' => $about?->shop_location,
            'business_type' => $about?->business_type,
            'owner_name' => $about?->owner_name,
            'start_date' => $startDate->setTimezone($timezone)->format('d F Y'),
            'end_date' => $endDate->subDay()->setTimezone($timezone)->format('d F Y'),
        ];
        $reports = [];
        $totalSelling = 0;
        $totalTransaction = 0;
        $totalItem = 0;
        $totalDiscount = 0;
        $totalProfit = 0;

        /** @var Selling $selling */
        foreach ($sellings as $selling) {
            /** @var SellingDetail $detail */
            foreach ($selling->sellingDetails as $detail) {

                $reports[] = [
                    'date' => Carbon::parse($selling->date, 'UTC')->setTimezone($timezone)->format('d/m/Y'),
                    'total_selling' => $this->formatCurrency($selling->total_selling),
                    'total_transaction' => $this->formatCurrency($selling->total_transaction),
                    'total_item' => $this->formatCurrency($selling->total_item),
                    'total_discount' => $this->formatCurrency($selling->total_discount),
                    'total_profit' => $this->formatCurrency($selling->total_profit),
                ];

                $totalSelling += $selling->total_selling;
                $totalTransaction += $selling->total_transaction;
                $totalItem += $selling->total_item;
                $totalDiscount += $selling->total_discount;
                $totalProfit += $selling->total_profit;
            }
        }

        $footer = [
            'total_selling' => $this->formatCurrency($totalSelling),
            'total_transaction' => $totalTransaction,
            'total_item' => $totalItem,
            'total_discount' => $this->formatCurrency($totalDiscount),
            'total_profit' => $this->formatCurrency($totalProfit),
        ];

        return [
            'reports' => $reports,
            'footer' => $footer,
            'header' => $header,
        ];
    }

    private function formatCurrency($value)
    {
        return Number::format($value, locale: config('app.locale'));
    }
}
