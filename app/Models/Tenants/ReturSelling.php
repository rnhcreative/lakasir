<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturSelling extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function sellingDetail(): BelongsTo
    {
        return $this->belongsTo(SellingDetail::class);
    }

    public function newProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'new_product_id');
    }
}
