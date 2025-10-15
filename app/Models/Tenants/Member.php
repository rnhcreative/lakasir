<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @mixin IdeHelperMember
 */
class Member extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function receivables(): HasMany
    {
        return $this->hasMany(Receivable::class);
    }

    public function sellings(): HasMany
    {
        return $this->hasMany(Selling::class);
    }

    /**
     * Get all of the deployments for the project.
     */
    public function receivablePayments(): HasManyThrough
    {
        return $this->hasManyThrough(ReceivablePayment::class, Receivable::class);
    }
}
