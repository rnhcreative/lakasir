<?php

namespace App\Services\Tenants;

use App\Models\Tenants\About;
use App\Models\Tenants\ReturSelling;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class ReturSellingReportService
{
    public function generate(array $data)
    {
        $timezone = config('setting.timezone');
        $about = About::first();
        $startDate = Carbon::parse($data['start_date'], $timezone)->setTimezone('UTC');
        $endDate = Carbon::parse($data['end_date'], $timezone)->addDay()->setTimezone('UTC');

        $records = ReturSelling::query()
            ->select()
            ->with(
                'sellingDetail.product',
                'sellingDetail.selling',
                'newProduct',
            )
            ->when($data['start_date'] && $data['end_date'], function (Builder $query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->orderBy('created_at', 'desc')
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

        $totalRefundAmount = 0;
        $totalAdditionalAmount = 0;

        foreach ($records as $record) {
            $reports[] = [
                'date' => $record->created_at->setTimezone($timezone)->format('d F Y'),
                'code' => $record->sellingDetail->selling->code,
                'retur_item_name' => $record->sellingDetail->product->name,
                'new_item_name' => $record->newProduct?->name,
                'qty' => $record->qty,
                'refund_amount' => $this->formatCurrency($record->refund_amount),
                'additional_amount' => $this->formatCurrency($record->additional_amount),
            ];

            $totalRefundAmount += $record->refund_amount;
            $totalAdditionalAmount += $record->additional_amount;
        }

        $footer = [
            'total_refund_amount' => $this->formatCurrency($totalRefundAmount),
            'total_additional_amount' => $this->formatCurrency($totalAdditionalAmount),
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
