<div class="max-w-full">
  <div class="text-center space-y-2">
    <h1 class="text-3xl font-semibold">{{ __('Selling Report') }}</h1>
    <h3 class="text-xl">{{ $header['shop_name'] }}</h3>
  </div>
  <p class="mb-4">{{ __('Period') }}: <b>{{ $header['start_date'] }} - {{ $header['end_date'] }}</b></p>

  <x-table class="w-full table-fixed">
    <x-table-header>
      <x-table-header-cell>@lang('Date')</x-table-header-cell>
      <x-table-header-cell>@lang('Selling')</x-table-header-cell>
      <x-table-header-cell>@lang('Transaction')</x-table-header-cell>
      <x-table-header-cell>@lang('Item')</x-table-header-cell>
      <x-table-header-cell>@lang('Discount')</x-table-header-cell>
      <x-table-header-cell>@lang('Profit')</x-table-header-cell>
    </x-table-header>

    <tbody>
      @foreach($reports as $key => $report)
        <x-table-row>
          <x-table-cell>{{ $report['date'] }}</x-table-cell>
          <x-table-cell class="number">{{ $report['total_selling'] }}</x-table-cell>
          <x-table-cell>{{ $report['total_transaction'] }}</x-table-cell>
          <x-table-cell>{{ $report['total_item'] }}</x-table-cell>
          <x-table-cell class="number">{{ $report['total_discount'] }}</x-table-cell>
          <x-table-cell class="number">{{ $report['total_profit'] }}</x-table-cell>
        </x-table-row>
      @endforeach
      <x-table-row class="bg-gray-200 dark:bg-gray-700 font-semibold">
        <x-table-cell>{{ __('Total') }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['total_selling'] }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['total_transaction'] }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['total_item'] }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['total_discount'] }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['total_profit'] }}</x-table-cell>
      </x-table-row>
    </tbody>
  </x-table>

</div>

