<?php

namespace App\Services\Tenants;

use App\Models\Tenants\About;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use App\Models\Tenants\Profile;
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
                members.identity_number,
                members.email,
                SUM(sellings.total_price) AS total_selling,
                COUNT(sellings.id) AS total_transaction
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

        $grandTotalTransaction = 0;
        $grandTotalSelling = 0;

        foreach ($results as $result) {
            $reports[] = [
                'name' => $result->name,
                'identity_number' => $result->identity_number,
                'email' => $result->email,
                'total_transaction' => $result->total_transaction,
                'total_selling' => $this->formatCurrency($result->total_selling),
            ];

            $grandTotalTransaction += $result->total_transaction;
            $grandTotalSelling += $result->total_selling;
        }

        return [
            'header' => $header,
            'reports' => $reports ?? [],
            'footer' => [
                'grand_total_transaction' => $grandTotalTransaction,
                'grand_total_selling' => $this->formatCurrency($grandTotalSelling),
            ],
        ];
    }

    private function formatCurrency($value)
    {
        return Number::format($value);
    }
}
