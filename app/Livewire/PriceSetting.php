<?php

namespace App\Livewire;

use App\Filament\Tenant\Resources\Traits\RefreshThePage;
use App\Models\Tenants\CartItem;
use Livewire\Component;

class PriceSetting extends Component
{
    use RefreshThePage;

    public CartItem $cartItem;

    public $unit = null;

    public $customPrice = null;

    public function changeThePrice(): void
    {
        $this->cartItem->update([
            'price_unit_id' => $this->unit,
        ]);

        if ($this->cartItem->priceUnit) {
            $this->cartItem->update([
                'price' => $this->cartItem->priceUnit->selling_price,
            ]);
        }

        if ($this->unit == 0 && $this->customPrice) {
            $this->cartItem->update([
                'price' => $this->customPrice,
            ]);
        }

        $this->refreshPage();

        $this->dispatch('close-modal', id : "price-setting-{$this->cartItem->id}");
    }

    public function removeThePrice(): void
    {
        $this->cartItem->update([
            'price_unit_id' => null,
            'price' => $this->cartItem->product->selling_price,
        ]);

        $this->unit = null;
        $this->customPrice = null;

        $this->refreshPage();

        $this->dispatch('close-modal', id : "price-setting-{$this->cartItem->id}");
    }

    public function render()
    {
        return view('livewire.price-setting');
    }
}
