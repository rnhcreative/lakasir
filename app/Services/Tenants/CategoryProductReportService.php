<?php

namespace App\Services\Tenants;

use App\Models\Tenants\About;
use App\Models\Tenants\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class CategoryProductReportService
{
    public function generate(array $data)
    {
        $timezone = config('setting.timezone');
        $about = About::first();
        $startDate = Carbon::parse($data['start_date'], $timezone);
        $endDate = Carbon::parse($data['end_date'], $timezone);

        $records = DB::select("
            SELECT
                categories.name,
                SUM((selling_details.price - selling_details.discount_price) * selling_details.qty) AS total_selling,
                COUNT(DISTINCT selling_details.selling_id) AS total_transaction,
                GROUP_CONCAT(DISTINCT selling_details.selling_id) AS transaction_ids,
                SUM(selling_details.qty) AS total_item,
                SUM(selling_details.discount_price) AS total_discount,
                SUM(((selling_details.price - selling_details.discount_price) * selling_details.qty) - selling_details.cost) AS total_profit
            FROM selling_details
            JOIN products ON selling_details.product_id = products.id
            JOIN categories ON products.category_id = categories.id
            JOIN sellings ON selling_details.selling_id = sellings.id
            WHERE DATE(CONVERT_TZ(sellings.date, 'UTC', ?)) BETWEEN ? AND ?
            GROUP BY categories.id
            ORDER BY SUM(sellings.total_price) DESC
        ", [
            config('setting.timezone'),
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
        ]);

        $reports = [];

        $totalSelling = 0;
        $totalTransaction = 0;
        $totalItem = 0;
        $totalDiscount = 0;
        $totalProfit = 0;
        $allTransactionIds = [];

        foreach ($records as $record) {

            $reports[] = [
                'category_name' => $record->name,
                'total_transaction' => $record->total_transaction,
                'total_selling' => $this->formatCurrency($record->total_selling),
                'total_item' => $record->total_item,
                'total_discount' => $this->formatCurrency($record->total_discount),
                'total_profit' => $this->formatCurrency($record->total_profit),
            ];

            $totalSelling += $record->total_selling;
            $totalItem += $record->total_item;
            $totalDiscount += $record->total_discount;
            $totalProfit += $record->total_profit;
            $allTransactionIds = array_merge($allTransactionIds, explode(',', $record->transaction_ids));
        }

        $header = [
            'shop_name' => $about?->shop_name,
            'shop_location' => $about?->shop_location,
            'business_type' => $about?->business_type,
            'owner_name' => $about?->owner_name,
            'start_date' => $startDate->format('d F Y'),
            'end_date' => $endDate->format('d F Y'),
        ];

        $footer = [
            'total_selling' => $this->formatCurrency($totalSelling),
            'total_transaction' => count(array_unique($allTransactionIds)),
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
