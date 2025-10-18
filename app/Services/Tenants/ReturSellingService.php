<?php

namespace App\Services\Tenants;

use App\Models\Tenants\Product;
use Illuminate\Support\Facades\DB;
use App\Models\Tenants\SellingDetail;
use Filament\Notifications\Notification;

class ReturSellingService
{
    public function processReturn(SellingDetail $sellingDetail, $returnData)
    {
        try {
            DB::beginTransaction();

            // Create return selling record
            $returSelling = $sellingDetail->returSelling()->create([
                'qty' => $returnData['qty'] ?? 0,
                'reason' => $returnData['reason'] ?? '',
                'refund_amount' => $returnData['refund_amount'] ?? 0,
                'additional_amount' => $returnData['additional_amount'] ?? 0,
                'new_product_id' => $returnData['new_product_id'] ?? null,
                'payment_method_id' => $returnData['payment_method_id'],
            ]);
            $sellingDetail->save();
            $returSelling->refresh();

            $stockService = new StockService();
            // add stock for returned product
            $stockService->addStock($sellingDetail->product, $returSelling->qty);

            // update stock if new product is provided
            if ($returSelling->newProduct) {
                // reduce stock for new product
                $stockService->reduceStock($returSelling->newProduct, $sellingDetail->returSelling->qty);
            }

            DB::commit();
        } catch (\Exception $e) {

            Notification::make()
                ->title(__('Return processing failed'))
                ->danger()
                ->send();

            DB::rollBack();
        }

        $sellingDetail->refresh();

        return $sellingDetail;
    }
}
