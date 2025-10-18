<div class="max-w-full">
  <div class="text-center space-y-2">
    <h1 class="text-3xl font-semibold">{{ __('Retur Selling Report') }}</h1>
    <h3 class="text-xl">{{ $header['shop_name'] }}</h3>
  </div>
  <p class="mb-4">{{ __('Period') }}: <b>{{ $header['start_date'] }} - {{ $header['end_date'] }}</b></p>

  <x-table class="w-full table-fixed">
    <x-table-header>
      <x-table-header-cell>@lang('Date')</x-table-header-cell>
      <x-table-header-cell>@lang('Selling Code')</x-table-header-cell>
      <x-table-header-cell>@lang('Item')</x-table-header-cell>
      <x-table-header-cell>@lang('Item yang ditukar')</x-table-header-cell>
      <x-table-header-cell>@lang('Qty')</x-table-header-cell>
      <x-table-header-cell>@lang('Refund amount')</x-table-header-cell>
      <x-table-header-cell>@lang('Additional amount')</x-table-header-cell>
    </x-table-header>

    <tbody>
      @foreach($reports as $key => $report)
        <x-table-row>
          <x-table-cell>{{ $report['date'] }}</x-table-cell>
          <x-table-cell>{{ $report['code'] }}</x-table-cell>
          <x-table-cell>{{ $report['retur_item_name'] }}</x-table-cell>
          <x-table-cell>{{ $report['new_item_name'] }}</x-table-cell>
          <x-table-cell>{{ $report['qty'] }}</x-table-cell>
          <x-table-cell class="number">{{ $report['refund_amount'] }}</x-table-cell>
          <x-table-cell class="number">{{ $report['additional_amount'] }}</x-table-cell>
        </x-table-row>
      @endforeach
      <x-table-row>
        <x-table-cell colspan="5">{{ __('Total') }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['total_refund_amount'] }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['total_additional_amount'] }}</x-table-cell>
      </x-table-row>
    </tbody>
  </x-table>
</div>

