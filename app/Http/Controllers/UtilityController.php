<?php

namespace App\Http\Controllers;

use Mpdf;
use App\Models\Tenants\About;
use App\Models\Tenants\Selling;
use Illuminate\Support\Carbon;

class UtilityController extends Controller
{
    public function print($sellingId)
    {
        $about = About::first();
        $selling = Selling::with(['sellingDetails.product', 'paymentMethod'])->findOrFail($sellingId);
        $currencySymbol = config('setting.currency_symbol');
        $shopName = $about->shop_name;
        $shopLocation = $about->shop_location;

        $data = [
            'code' => $selling->code,
            'date' => Carbon::parse($selling->date)->format('d F Y'),
            'time' => Carbon::parse($selling->date)->format('h:i'),
            'items' => $selling->sellingDetails,
            'selling' => $selling,
            'sub_total' => $selling->sellingDetails->sum(fn ($item) => $item->price * $item->qty),
            'currency_symbol' => $currencySymbol,
            'shop_name' => $shopName,
            'shop_location' => $shopLocation,
            'cashier_name' => $selling->user->name,
            'payment_method' => $selling->paymentMethod->name,
        ];

        $html = view('print.invoice', $data)->render();

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                public_path(''),
            ]),
            'fontdata' => $fontData + [
                'terminus' => [
                    'R' => 'Terminus.ttf',
                ]
            ],
            'default_font' => 'terminus',
            'mode' => 'utf-8',
            'shrink_tables_to_fit' => 0,
            'format' => [58, 200],
            'orientation' => 'P',
            'margin_left' => 3,
            'margin_right' => 3,
            'margin_top' => 3,
            'margin_bottom' => 3,
        ]);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('nota-'. strtolower($selling->code) .'.pdf', 'I');
    }
}
