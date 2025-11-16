<?php

namespace App\Services\Tenants;
use App\Models\Tenants\About;
use FontLib\Table\Type\loca;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashflowService
{
    public function generate(array $data)
    {
        $timezone = config('setting.timezone');
        $about = About::first();
        $startDate = Carbon::parse($data['start_date'], config('setting.timezone'));
        $endDate = Carbon::parse($data['end_date'], config('setting.timezone'));

        // Implementation for generating cashflow report
        $inCashflow = collect();

        $inCashflow = $inCashflow
            ->merge(
                DB::table('sellings')
                    ->join('payment_methods', 'sellings.payment_method_id', '=', 'payment_methods.id')
                    ->join('members', 'sellings.member_id', '=', 'members.id')
                    ->where(function ($q) {
                        $q->where('payment_methods.is_cash', true)
                            ->orWhere('payment_methods.is_debit', true)
                            ->orWhere('payment_methods.is_wallet', true);
                    })
                    ->select(
                        'sellings.date',
                        DB::raw('(sellings.total_price - sellings.discount_price - sellings.tax_price) as amount'),
                        DB::raw("'Penjualan' as source"),
                        DB::raw("'debit' as type"),
                        DB::raw("CONCAT('Penjualan #', sellings.code, ' a/n ', members.name) as note"),
                        DB::raw('payment_methods.id as payment_method_id'),
                        DB::raw('payment_methods.name as payment_method_name')
                    )
                    ->whereRaw("DATE(CONVERT_TZ(sellings.date, 'UTC', ?)) BETWEEN ? AND ?", [config('setting.timezone'), $startDate, $endDate])
                    ->get()
            )
            ->merge(
                DB::table('receivable_payments')
                    ->join('payment_methods', 'receivable_payments.payment_method_id', '=', 'payment_methods.id')
                    ->join('receivables', 'receivable_payments.receivable_id', '=', 'receivables.id')
                    ->join('members', 'receivables.member_id', '=', 'members.id')
                    ->where(function ($q) {
                        $q->where('payment_methods.is_cash', true)
                            ->orWhere('payment_methods.is_debit', true)
                            ->orWhere('payment_methods.is_wallet', true);
                    })
                    ->whereRaw("DATE(CONVERT_TZ(receivable_payments.date, 'UTC', ?)) BETWEEN ? AND ?", [config('setting.timezone'), $startDate, $endDate])
                    ->select(
                        'receivable_payments.date',
                        'receivable_payments.amount',
                        DB::raw("'Pelunasan Piutang' as source"),
                        DB::raw("'debit' as type"),
                        DB::raw("CONCAT('Pembayaran Utang dari ', members.name) as note"),
                        DB::raw('payment_methods.id as payment_method_id'),
                        DB::raw('payment_methods.name as payment_method_name'),
                    )
                    ->get()
            )
            ->merge(
                DB::table('retur_sellings')
                    ->join('payment_methods', 'retur_sellings.payment_method_id', '=', 'payment_methods.id')
                    ->join('selling_details', 'retur_sellings.selling_detail_id', '=', 'selling_details.id')
                    ->join('sellings', 'selling_details.selling_id', '=', 'sellings.id')
                    ->select(
                        DB::raw('retur_sellings.created_at as date'),
                        DB::raw('(retur_sellings.additional_amount) as amount'),
                        DB::raw("'Retur Penjualan' as source"),
                        DB::raw("'debit' as type"),
                        DB::raw("CONCAT('Retur Penjualan #', sellings.code) as note"),
                        DB::raw('payment_methods.id as payment_method_id'),
                        DB::raw('payment_methods.name as payment_method_name')
                    )
                    ->whereRaw("DATE(CONVERT_TZ(retur_sellings.created_at, 'UTC', ?)) BETWEEN ? AND ?", [config('setting.timezone'), $startDate, $endDate])
                    ->where('retur_sellings.additional_amount', '>', 0)
                    ->get()
            );

        $outCashflow = collect();

        $outCashflow = $outCashflow
            ->merge(
                DB::table('expenses')
                    ->join('payment_methods', 'expenses.payment_method_id', '=', 'payment_methods.id')
                    ->select(
                        DB::raw('expenses.expense_date as date'),
                        'expenses.amount',
                        DB::raw("'Pengeluaran' as source"),
                        DB::raw("'credit' as type"),
                        DB::raw("CONCAT('Pengeluaran: ', note) as note"),
                        DB::raw('payment_methods.id as payment_method_id'),
                        DB::raw('payment_methods.name as payment_method_name')
                    )
                    ->whereRaw("DATE(CONVERT_TZ(expenses.expense_date, 'UTC', ?)) BETWEEN ? AND ?", [config('setting.timezone'), $startDate, $endDate])
                    ->get()
            )
            ->merge(
                DB::table('retur_sellings')
                    ->join('payment_methods', 'retur_sellings.payment_method_id', '=', 'payment_methods.id')
                    ->join('selling_details', 'retur_sellings.selling_detail_id', '=', 'selling_details.id')
                    ->join('sellings', 'selling_details.selling_id', '=', 'sellings.id')
                    ->select(
                        DB::raw('retur_sellings.created_at as date'),
                        DB::raw('(retur_sellings.refund_amount) as amount'),
                        DB::raw("'Retur Penjualan' as source"),
                        DB::raw("'credit' as type"),
                        DB::raw("CONCAT('Retur Penjualan #', sellings.code) as note"),
                        DB::raw('payment_methods.id as payment_method_id'),
                        DB::raw('payment_methods.name as payment_method_name')
                    )
                    ->whereRaw("DATE(CONVERT_TZ(retur_sellings.created_at, 'UTC', ?)) BETWEEN ? AND ?", [config('setting.timezone'), $startDate, $endDate])
                    ->where('retur_sellings.refund_amount', '>', 0)
                    ->get()
            );

        // Gabungkan semua jadi satu daftar arus kas
        // dan urutkan berdasarkan tanggal tetapi pemasukan sebelum pengeluaran pada tanggal yang sama
        $cashFlows = $inCashflow->merge($outCashflow)->sortBy(function ($item) {
            return Carbon::parse($item->date)->format('YmdHis') . ($item->type === 'debit' ? '0' : '1');
        });

        $groupedCashFlows = $cashFlows->groupBy('payment_method_name');

        $reports = $groupedCashFlows->map(function ($items, $paymentMethodName) {
            $saldo = 0;
            $rows = $items->sortBy('date')->map(function ($row) use (&$saldo) {
                $saldo += ($row->type === 'debit' ? $row->amount : -$row->amount);
                return (object)[
                    'date' => Carbon::parse($row->date)->setTimezone(config('setting.timezone'))->format('d/m/Y'),
                    'source' => $row->source,
                    'debit' => $row->type === 'debit' ? $this->formatCurrency($row->amount) : '',
                    'credit' => $row->type === 'credit' ? $this->formatCurrency($row->amount) : '',
                    'origin_debit' => $row->type === 'debit' ? $row->amount : 0,
                    'origin_credit' => $row->type === 'credit' ? $row->amount : 0,
                    'saldo' => $this->formatCurrency($saldo),
                    'note' => $row->note,
                ];
            });

            return [
                'method' => $paymentMethodName,
                'rows' => $rows,
                'total_debit' => $this->formatCurrency($rows->sum('origin_debit')),
                'total_credit' => $this->formatCurrency($rows->sum('origin_credit')),
                'ending_balance' => $this->formatCurrency($saldo),
            ];
        });

        return [
            'header' => [
                'shop_name' => $about?->shop_name,
                'shop_location' => $about?->shop_location,
                'business_type' => $about?->business_type,
                'owner_name' => $about?->owner_name,
                'start_date' => $startDate->format('d F Y'),
                'end_date' => $endDate->format('d F Y'),
            ],
            'reports' => $reports,
            'footer' => [],
        ];

        // // Hitung saldo berjalan
        // $balance = 0;
        // $cashFlows = $cashFlows->map(function ($row) use (&$balance) {
        //     $balance += ($row->type === 'debit' ? $row->amount : -$row->amount);
        //     return (object)[
        //         'date' => $row->date,
        //         'source' => $row->source,
        //         'type' => $row->type,
        //         'debit' => $row->type === 'debit' ? $row->amount : 0,
        //         'credit' => $row->type === 'credit' ? $row->amount : 0,
        //         'saldo' => $balance,
        //     ];
        // });
    }

    private function formatCurrency($value)
    {
        return Number::format($value, locale: config('setting.locale'));
    }
}
