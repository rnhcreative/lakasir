<?php

namespace App\Services\Tenants;

use App\Models\Tenants\About;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\DB;

class MemberReportService
{
    public function generate(array $data)
    {
        $timezone = config('setting.timezone');
        $about = About::first();
        $tzName = Carbon::parse($data['start_date'])->getTimezone()->getName();
        $startDate = Carbon::parse($data['start_date'], $timezone)->setTimezone('UTC');
        $endDate = Carbon::parse($data['end_date'], $timezone)->addDay()->setTimezone('UTC');

        $header = [
            'shop_name' => $about?->shop_name,
            'shop_location' => $about?->shop_location,
            'business_type' => $about?->business_type,
            'owner_name' => $about?->owner_name,
            'start_date' => $startDate->setTimezone($timezone)->format('d F Y'),
            'end_date' => $endDate->subDay()->setTimezone($timezone)->format('d F Y'),
        ];

        $results = DB::select("
            SELECT
                members.name,
                members.email,
                SUM(sellings.total_price) AS total_selling,
                COUNT(sellings.id) AS total_transaction,
                SUM(total_qty) AS total_item,
                SUM(discount_price) AS total_discount,
                SUM(total_price - total_cost) AS total_profit
            FROM sellings
            JOIN members ON sellings.member_id = members.id
            WHERE sellings.member_id IS NOT NULL
            AND sellings.date BETWEEN ? AND ?
            GROUP BY members.id
            ORDER BY SUM(sellings.total_price) DESC
        ", [
            $startDate->toDateTimeString(),
            $endDate->toDateTimeString(),
        ]);

        $totalSelling = 0;
        $totalTransaction = 0;
        $totalItem = 0;
        $totalDiscount = 0;
        $totalProfit = 0;

        foreach ($results as $result) {
            $reports[] = [
                'name' => $result->name,
                'email' => $result->email,
                'total_transaction' => $result->total_transaction,
                'total_selling' => $this->formatCurrency($result->total_selling),
                'total_item' => $result->total_item,
                'total_discount' => $this->formatCurrency($result->total_discount),
                'total_profit' => $this->formatCurrency($result->total_profit),
            ];

            $totalSelling += $result->total_selling;
            $totalTransaction += $result->total_transaction;
            $totalItem += $result->total_item;
            $totalDiscount += $result->total_discount;
            $totalProfit += $result->total_profit;
        }

        return [
            'header' => $header,
            'reports' => $reports ?? [],
            'footer' => [
                'total_selling' => $this->formatCurrency($totalSelling),
                'total_transaction' => $totalTransaction,
                'total_item' => $totalItem,
                'total_discount' => $this->formatCurrency($totalDiscount),
                'total_profit' => $this->formatCurrency($totalProfit),
            ],
        ];
    }

    private function formatCurrency($value)
    {
        return Number::format($value, locale: config('app.locale'));
    }
}
