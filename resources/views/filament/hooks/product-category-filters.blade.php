<div class="flex justify-end space-x-2">
    <x-filament::button
        :outlined="$resource->activeCategory !== null"
        wire:click="setActiveCategory(null)"
    >
        Semua
    </x-filament::button>

    @foreach(\App\Models\Tenants\Category::all() as $category)
        <x-filament::button
            :outlined="$resource->activeCategory !== $category->id"
            wire:click="setActiveCategory({{ $category->id }})"
        >
            {{ $category->name }}
        </x-filament::button>
    @endforeach
</div>
