<?php

namespace App\Services\Tenants;

use App\Models\Tenants\SellingDetail;


class ReturnSellingService
{
    public function processReturn(SellingDetail $sellingDetail, $returnData)
    {
        $sellingDetail->returSelling()->create([
            'qty' => $returnData['qty'] ?? 0,
            'reason' => $returnData['reason'] ?? '',
            'refund_amount' => $returnData['refund_amount'] ?? 0,
            'additional_amount' => $returnData['additional_amount'] ?? 0,
            'new_product_id' => $returnData['new_product_id'] ?? null,
        ]);
        $sellingDetail->save();
        $sellingDetail->refresh();

        return $sellingDetail;
    }
}
