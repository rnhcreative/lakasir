<div class="max-w-full">
  <div class="text-center space-y-2">
    <h1 class="text-3xl font-semibold">{{ __('Selling Report') }}</h1>
    <h3 class="text-xl">{{ $header['shop_name'] }}</h3>
  </div>
  <p class="mb-4">{{ __('Period') }}: <b>{{ $header['start_date'] }} - {{ $header['end_date'] }}</b></p>

  <x-table class="w-full table-fixed">
    <x-table-header>
      <x-table-header-cell>@lang('Date')</x-table-header-cell>
      <x-table-header-cell>@lang('Selling Code')</x-table-header-cell>
      <x-table-header-cell>@lang('Product Name')</x-table-header-cell>
      <x-table-header-cell>@lang('Price')</x-table-header-cell>
      <x-table-header-cell>@lang('Qty')</x-table-header-cell>
      <x-table-header-cell>@lang('Sub Total')</x-table-header-cell>
      <x-table-header-cell>@lang('Discount')</x-table-header-cell>
      <x-table-header-cell>@lang('Total')</x-table-header-cell>
    </x-table-header>

    <tbody>
      @foreach($reports as $key => $report)
        <x-table-row>
          <x-table-cell>{{ $report['date'] }}</x-table-cell>
          <x-table-cell>{{ $report['code'] }}</x-table-cell>
          <x-table-cell>{{ $report['name'] }}</x-table-cell>
          <x-table-cell class="number">{{ $report['selling_price'] }}</x-table-cell>
          <x-table-cell>{{ $report['qty'] }}</x-table-cell>
          <x-table-cell class="number">{{ $report['selling'] }}</x-table-cell>
          <x-table-cell class="number">{{ $report['discount_price'] }}</x-table-cell>
          <x-table-cell class="number">{{ $report['total_after_discount'] }}</x-table-cell>
        </x-table-row>
      @endforeach
      <x-table-row>
        <x-table-cell colspan="4">{{ __('Total') }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['total_qty'] }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['total_before_discount'] }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['total_all_discount'] }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['total_after_discount'] }}</x-table-cell>
      </x-table-row>
    </tbody>
  </x-table>

</div>

