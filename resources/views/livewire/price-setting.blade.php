<div>
  @if($cartItem?->product?->priceUnits?->count() > 0)
    <button
      class="bg-info-400 rounded-lg px-2 py-1"
      wire:loading.attr="disabled"
      x-on:mousedown="$dispatch('open-modal', {id: 'price-setting-{{$cartItem->id}}', data: { id: {{ $cartItem->id }} } })"
    >
      <div class="flex items-center gap-x-1">
        <x-heroicon-o-wrench-screwdriver class="dark:text-white text-black h-6 w-4"/> <p class="hidden lg:block">@lang('Price')</p>
      </div>
    </button>
      <x-filament::modal id="price-setting-{{$cartItem->id}}" x-data="{ unit: $wire.entangle('unit') }">
      <x-slot name="heading">
          <p>{{ __('Choose the unit price') }}</p>
      </x-slot>

      @foreach ($cartItem->product->priceUnits as $priceUnit)
          <div class="flex items-center gap-x-4">
              <input type="radio" id="unit-{{ $priceUnit->id }}" value="{{ $priceUnit->id }}" x-model="unit">
              <label for="unit-{{ $priceUnit->id }}">{{ $priceUnit->unit }} - {{ price_format($priceUnit->selling_price) }}</label>
          </div>
      @endforeach

      <div class="flex items-center gap-x-4 uppercase">
          <input type="radio" id="unit-0" value="0" x-model="unit">
          <label for="unit-0">{{ __('Custom') }}</label>

          <div class="ml-4">
              <input type="number"
                  wire:model="customPrice"
                  class="border border-gray-300 rounded px-2 py-1 max-w-xs"
                  placeholder="{{ __('Enter price') }}"
                  x-bind:readonly="unit != 0"
              />
          </div>
      </div>

      <div class="grid grid-cols-2 gap-x-2 mt-5 w-full">
          <x-filament::button type="button" wire:click="changeThePrice">
              @lang('Changes')
          </x-filament::button>

          <button type="button"
              wire:click="removeThePrice"
              class="py-1 px-4 bg-danger-600 text-white rounded-lg flex gap-x-1 items-center justify-center">
              <x-heroicon-o-trash class="dark:text-white text-black h-4 w-4"/>
              <span>@lang('Remove')</span>
          </button>
      </div>
  </x-filament::modal>
  @endif
</div>
