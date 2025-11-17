<div class="row">
  <div class="flex justify-end space-x-2">
      <x-filament::button
          :outlined="$resource->activeCategory !== null"
          wire:click="setActiveCategory(null)"
      >
          Semua
      </x-filament::button>

      @foreach(\App\Models\Tenants\Category::orderBy('name')->get() as $category)
          <x-filament::button
              :outlined="$resource->activeCategory !== $category->id"
              wire:click="setActiveCategory({{ $category->id }})"
          >
              {{ $category->name }}
          </x-filament::button>
      @endforeach
  </div>
  <div class="clear-both"></div>
  <div>
    <button wire:click="toggleShowProductImage()" class="py-1 px-4 flex justify-center items-center bg-gray-100 rounded-lg gap-x-1 text-gray-800 mt-4 border border-gray-300">
      {{ $resource->showProductImage ? 'Sembunyikan Gambar Produk' : 'Tampilkan Gambar Produk'}}
    </button>
  </div>
</div>
