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
        $startDate = Carbon::parse($data['start_date'], $timezone);
        $endDate = Carbon::parse($data['end_date'], $timezone);

        $sellings = DB::select("
                SELECT
                    sellings.date,
                    SUM(sellings.total_price) AS total_selling,
                    COUNT(sellings.id) AS total_transaction,
                    SUM(total_qty) AS total_item,
                    SUM(discount_price) AS total_discount,
                    SUM(total_price - total_cost) AS total_profit
                FROM sellings
                JOIN employees ON sellings.employee_id = employees.id
                WHERE sellings.employee_id IS NOT NULL
                AND DATE(CONVERT_TZ(sellings.date, 'UTC', ?)) BETWEEN ? AND ?
                GROUP BY DATE(CONVERT_TZ(sellings.date, 'UTC', ?))
                ORDER BY DATE(CONVERT_TZ(sellings.date, 'UTC', ?)) ASC
            ", [
                config('setting.timezone'),
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
                config('setting.timezone'),
                config('setting.timezone'),
            ]);

        $header = [
            'shop_name' => $about?->shop_name,
            'shop_location' => $about?->shop_location,
            'business_type' => $about?->business_type,
            'owner_name' => $about?->owner_name,
            'start_date' => $startDate->format('d F Y'),
            'end_date' => $endDate->format('d F Y'),
        ];
        $reports = [];
        $totalSelling = 0;
        $totalTransaction = 0;
        $totalItem = 0;
        $totalDiscount = 0;
        $totalProfit = 0;

        /** @var Selling $selling */
        foreach ($sellings as $selling) {
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
