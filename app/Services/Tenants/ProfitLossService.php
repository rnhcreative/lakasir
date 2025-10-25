<?php

namespace App\Services\Tenants;

use App\Models\Tenants\About;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\DB;

class ProfitLossService
{
    public function generate(array $data)
    {
        $startDate = Carbon::parse($data['start_date'], config('setting.timezone'));
        $endDate = Carbon::parse($data['end_date'], config('setting.timezone'));
        // get total selling credit and non credit by payment method ID
        $selling = DB::select("
            SELECT
                SUM(CASE
                    WHEN pm.is_credit = 1 THEN 0
                    ELSE (s.total_price - s.discount_price - s.tax_price)
                END) AS total_non_credit,
                SUM(CASE
                    WHEN pm.is_credit = 1 THEN (s.total_price - s.discount_price - s.tax_price)
                    ELSE 0
                END) AS total_credit,
                SUM(s.discount_price) AS total_discount,
                SUM(s.total_cost) as total_cost
            FROM sellings s
            JOIN payment_methods pm ON s.payment_method_id = pm.id
            WHERE DATE(CONVERT_TZ(s.date, 'UTC', ?)) BETWEEN ? AND ?
        ", [config('setting.timezone'), $startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        // get total retur selling
        $retur = DB::select("
            SELECT
                SUM(rs.refund_amount) AS total_retur_selling
            FROM retur_sellings rs
            WHERE rs.refund_amount > 0 AND DATE(CONVERT_TZ(rs.created_at, 'UTC', ?)) BETWEEN ? AND ?
        ", [config('setting.timezone'), $startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        // get total expense
        $expense = DB::select("
            SELECT
                SUM(amount) AS total_expense
            FROM expenses
            WHERE DATE(CONVERT_TZ(expense_date, 'UTC', ?)) BETWEEN ? AND ?
        ", [config('setting.timezone'), $startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        $totalNonCreditSelling = $selling[0]->total_non_credit ?? 0;
        $totalCreditSelling = $selling[0]->total_credit ?? 0;
        $totalDiscount = $selling[0]->total_discount ?? 0;
        $totalReturSelling = $retur[0]->total_retur_selling ?? 0;
        $totalNetSelling = ($totalNonCreditSelling + $totalCreditSelling) - $totalReturSelling;
        $hpp = $selling[0]->total_cost ?? 0;
        $totalGrossProfit = $totalNetSelling - $hpp;
        $totalExpense = $expense[0]->total_expense ?? 0;
        $totalNetProfit = $totalGrossProfit - $totalExpense;

        $about = About::first();
        $timezone = config('setting.timezone');

        return [
            'header' => [
                'shop_name' => $about?->shop_name,
                'shop_location' => $about?->shop_location,
                'business_type' => $about?->business_type,
                'owner_name' => $about?->owner_name,
                'start_date' => $startDate->setTimezone($timezone)->format('d F Y'),
                'end_date' => $endDate->subDay()->setTimezone($timezone)->format('d F Y'),
            ],
            'reports' => [
                'total_non_credit_selling' => $this->formatCurrency($totalNonCreditSelling),
                'total_credit_selling' => $this->formatCurrency($totalCreditSelling),
                'total_discount' => $this->formatCurrency($totalDiscount),
                'total_gross_selling' => $this->formatCurrency($totalNonCreditSelling + $totalCreditSelling),
                'total_retur_selling' => $this->formatCurrency($totalReturSelling),
                'total_net_selling' => $this->formatCurrency($totalNetSelling),
                'hpp' => $this->formatCurrency($hpp),
                'total_gross_profit' => $this->formatCurrency($totalGrossProfit),
                'total_expense' => $this->formatCurrency($totalExpense),
                'total_net_profit' => $this->formatCurrency($totalNetProfit),
            ],
            'footer' => [],
        ];
    }

    private function formatCurrency($value)
    {
        return Number::format($value, locale: config('setting.locale'));
    }
}
