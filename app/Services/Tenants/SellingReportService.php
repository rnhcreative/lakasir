<?php

namespace App\Services\Tenants;

use App\Models\Tenants\About;
use App\Models\Tenants\Profile;
use App\Models\Tenants\Selling;
use App\Models\Tenants\SellingDetail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class SellingReportService
{
    public function generate(array $data)
    {
        $timezone = config('setting.timezone');
        $about = About::first();
        $startDate = Carbon::parse($data['start_date'], $timezone)->setTimezone('UTC');
        $endDate = Carbon::parse($data['end_date'], $timezone)->addDay()->setTimezone('UTC');

        $sellings = Selling::query()
            ->select()
            ->with(
                'sellingDetails:id,selling_id,product_id,qty,price,cost,discount_price',
                'sellingDetails.product:id,name,initial_price,selling_price,sku',
                'user:id,name,email'
            )
            ->when($data['start_date'] && $data['end_date'], function (Builder $query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
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
        $totalQty = 0;
        $totalBeforeDiscount = 0;
        $totalAllDiscount = 0;
        $totalAfterDiscount = 0;

        /** @var Selling $selling */
        foreach ($sellings as $selling) {
            /** @var SellingDetail $detail */
            foreach ($selling->sellingDetails as $detail) {
                $subTotal = ($detail->price - ($detail->discount_price ?? 0)) * $detail->qty;
                $subTotalAfterDiscount = ($detail->price - ($detail->discount_price ?? 0)) * $detail->qty;

                $reports[] = [
                    'date' => Carbon::parse($selling->date, 'UTC')->setTimezone($timezone)->format('d/m/Y'),
                    'code' => $selling->code,
                    'name' => $detail->product->name,
                    'selling_price' => $this->formatCurrency($detail->price),
                    'selling' => $this->formatCurrency($subTotal),
                    'discount_price' => $this->formatCurrency($detail->discount_price ?? 0),
                    'initial_price' => $this->formatCurrency($detail->cost / $detail->qty),
                    'qty' => $detail->qty,
                    'cost' => $detail->cost,
                    'total_after_discount' => $this->formatCurrency($subTotalAfterDiscount),
                    'net_profit' => $this->formatCurrency(($detail->price - ($detail->discount_price ?? 0)) - $detail->cost),
                    'gross_profit' => $this->formatCurrency($detail->price - $detail->cost),
                ];

                $totalQty += $detail->qty;
                $totalBeforeDiscount += $subTotal;
                $totalAllDiscount += $detail->discount_price ?? 0;
                $totalAfterDiscount += $subTotalAfterDiscount;
            }
        }

        $footer = [
            'total_qty' => $totalQty,
            'total_before_discount' => $this->formatCurrency($totalBeforeDiscount),
            'total_all_discount' => $this->formatCurrency($totalAllDiscount),
            'total_after_discount' => $this->formatCurrency($totalAfterDiscount),
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
