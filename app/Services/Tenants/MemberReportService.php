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
        $startDate = Carbon::parse($data['start_date'], $timezone);
        $endDate = Carbon::parse($data['end_date'], $timezone);

        $results = DB::select("
            SELECT
                COALESCE(members.name, 'Toko') AS name,
                COALESCE(members.email, '') AS email,
                SUM(sellings.total_price) AS total_selling,
                COUNT(sellings.id) AS total_transaction,
                SUM(total_qty) AS total_item,
                SUM(discount_price) AS total_discount,
                SUM(total_price - total_cost) AS total_profit
            FROM sellings
            LEFT JOIN members ON sellings.member_id = members.id
            WHERE
                DATE(CONVERT_TZ(sellings.date, 'UTC', ?)) BETWEEN ? AND ?
            GROUP BY COALESCE(members.id, 'no_member')
            ORDER BY SUM(sellings.total_price) DESC
        ", [
            config('setting.timezone'),
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
        ]);

        $header = [
            'shop_name' => $about?->shop_name,
            'shop_location' => $about?->shop_location,
            'business_type' => $about?->business_type,
            'owner_name' => $about?->owner_name,
            'start_date' => $startDate->format('d F Y'),
            'end_date' => $endDate->format('d F Y'),
        ];

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
