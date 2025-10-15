<?php

namespace App\Services\Tenants;

use App\Models\Tenants\About;
use App\Models\Tenants\Profile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class ReceivableReportService
{
    public function generate(array $data)
    {
        $timezone = Profile::get()->timezone;
        $about = About::first();
        $startDate = Carbon::parse($data['start_date'], $timezone)->setTimezone('UTC');
        $endDate = Carbon::parse($data['end_date'], $timezone)->addDay()->setTimezone('UTC');
        $type = $data['type'] ?? 'all';

        $header = [
            'shop_name' => $about?->shop_name,
            'shop_location' => $about?->shop_location,
            'business_type' => $about?->business_type,
            'owner_name' => $about?->owner_name,
            'start_date' => $startDate->setTimezone($timezone)->format('d F Y'),
            'end_date' => $endDate->subDay()->setTimezone($timezone)->format('d F Y'),
        ];
        $reports = [];

        $types = $type === 'all'
            ? ['debt', 'payment']
            : [$type];

        $placeholders = implode(',', array_fill(0, count($types), '?'));

        $sql = "
            SELECT *
            FROM (
                SELECT
                    members.name AS member_name,
                    members.email AS member_email,
                    'debt' AS type,
                    receivables.created_at AS date,
                    receivables.total_receivable AS amount,
                    sellings.code AS selling_code,
                    payment_methods.name AS payment_method
                FROM receivables
                JOIN sellings ON receivables.selling_id = sellings.id
                JOIN members ON sellings.member_id = members.id
                JOIN payment_methods ON sellings.payment_method_id = payment_methods.id
                WHERE receivables.created_at BETWEEN ? AND ?

                UNION ALL

                SELECT
                    members.name AS member_name,
                    members.email AS member_email,
                    'payment' AS type,
                    receivable_payments.created_at AS date,
                    receivable_payments.amount AS amount,
                    sellings.code AS selling_code,
                    payment_methods.name AS payment_method
                FROM receivable_payments
                JOIN receivables ON receivable_payments.receivable_id = receivables.id
                JOIN sellings ON receivables.selling_id = sellings.id
                JOIN members ON sellings.member_id = members.id
                JOIN payment_methods ON receivable_payments.payment_method_id = payment_methods.id
                WHERE receivable_payments.created_at BETWEEN ? AND ?
            ) AS combined
            WHERE combined.type IN ($placeholders)
            ORDER BY combined.date ASC
        ";

        $records = DB::select($sql, [
            $startDate, $endDate, // for first BETWEEN
            $startDate, $endDate, // for second BETWEEN
            ...$types,            // for IN clause
        ]);

        $totalDebt = 0;
        $totalPayment = 0;

        foreach ($records as $record) {
            $reports[] = [
                'member_name' => $record->member_name,
                'member_email' => $record->member_email,
                'type' => $record->type,
                'date' => Carbon::parse($record->date, 'UTC')->setTimezone($timezone)->format('d F Y'),
                'amount' => $this->formatCurrency($record->amount),
                'selling_code' => $record->selling_code,
                'payment_method' => $record->payment_method,
            ];

            if ($record->type === 'debt') {
                $totalDebt += $record->amount;
            } elseif ($record->type === 'payment') {
                $totalPayment += $record->amount;
            }
        }

        $footer = [
            'total_debt' => $this->formatCurrency($totalDebt),
            'total_payment' => $this->formatCurrency($totalPayment),
            'total_rest_debt' => $this->formatCurrency($totalDebt - $totalPayment),
        ];

        return [
            'reports' => $reports,
            'footer' => $footer,
            'header' => $header,
        ];
    }

    private function formatCurrency($value)
    {
        return Number::format($value);
    }
}
