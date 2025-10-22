<?php

namespace App\Services\Tenants;

use App\Models\Tenants\About;
use App\Models\Tenants\Profile;
use App\Models\Tenants\Expense;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class ExpenseReportService
{
    public function generate(array $data)
    {
        $timezone = config('setting.timezone');
        $about = About::first();
        $startDate = Carbon::parse($data['start_date'], $timezone);
        $endDate = Carbon::parse($data['end_date'], $timezone);

        $records = Expense::query()
            ->with(['expenseType', 'paymentMethod'])
            ->when($data['start_date'] && $data['end_date'], function (Builder $query) use ($startDate, $endDate) {
                $query->whereBetween('expense_date', [$startDate, $endDate]);
            })
            ->whereRaw("DATE(CONVERT_TZ(expense_date, 'UTC', ?)) BETWEEN ? AND ?", [
                config('setting.timezone'),
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
            ])
            ->orderBy('expense_date', 'desc')
            ->get();

        $header = [
            'shop_name' => $about?->shop_name,
            'shop_location' => $about?->shop_location,
            'business_type' => $about?->business_type,
            'owner_name' => $about?->owner_name,
            'start_date' => $startDate->format('d F Y'),
            'end_date' => $endDate->format('d F Y'),
        ];
        $reports = [];

        $total = 0;

        /** @var Selling $selling */
        foreach ($records as $record) {
            $reports[] = [
                'date' => Carbon::parse($record->expense_date, 'UTC')->setTimezone($timezone)->format('d F Y'),
                'type' => $record->expenseType?->name,
                'amount' => $this->formatCurrency($record->amount),
                'payment_method' => $record->paymentMethod?->name,
                'note' => $record->note,
            ];
            $total += $record->amount;
        }

        $footer = [
            'total' => $this->formatCurrency($total),
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
