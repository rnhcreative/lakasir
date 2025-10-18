<div class="max-w-full">
  <div class="text-center space-y-2">
    <h1 class="text-3xl font-semibold">{{ __('Product Report') }}</h1>
    <h3 class="text-xl">{{ $header['shop_name'] }}</h3>
  </div>
  <p class="mb-4">{{ __('Period') }}: <b>{{ $header['start_date'] }} - {{ $header['end_date'] }}</b></p>
  <x-table class="w-full table-fixed">
    <x-table-header>
      <x-table-header-cell>SKU</x-table-header-cell>
      <x-table-header-cell>{{ __('Product Name') }}</x-table-header-cell>
      <x-table-header-cell>{{ __('Qty') }}</x-table-header-cell>
      <x-table-header-cell class="number">{{ __('Selling') }}</x-table-header-cell>
    </x-table-header>
    <tbody>
      @foreach($reports as $key => $report)
        <x-table-row>
          <x-table-cell>{{ $report['sku'] }}</x-table-cell>
          <x-table-cell>{{ $report['name'] }}</x-table-cell>
          <x-table-cell>{{ $report['qty'] }}</x-table-cell>
          <x-table-cell class="number">{{ $report['total_after_discount'] }}</x-table-cell>
        </x-table-row>
      @endforeach
      <x-table-row>
        <x-table-cell colspan="2">{{ __('Total') }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['total_qty'] }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['total_net'] }}</x-table-cell>
      </x-table-row>
    </tbody>
  </x-table>
</div>
