<div class="max-w-full">
  <div class="text-center space-y-2">
    <h1 class="text-3xl font-semibold">{{ __('Expense Report') }}</h1>
    <h3 class="text-xl">{{ $header['shop_name'] }}</h3>
  </div>
  <p class="mb-4">{{ __('Period') }}: <b>{{ $header['start_date'] }} - {{ $header['end_date'] }}</b></p>

  <x-table class="w-full table-fixed">
    <x-table-header>
      <x-table-header-cell>@lang('Date')</x-table-header-cell>
      <x-table-header-cell>@lang('Expense Type')</x-table-header-cell>
      <x-table-header-cell>@lang('Amount')</x-table-header-cell>
      <x-table-header-cell>@lang('Note')</x-table-header-cell>
      <x-table-header-cell>@lang('Payment Method')</x-table-header-cell>
    </x-table-header>

    <tbody>

      @foreach ($reports as $key => $report)
        <x-table-row>
          <x-table-cell>{{ $report['date'] }}</x-table-cell>
          <x-table-cell>{{ $report['type'] }}</x-table-cell>
          <x-table-cell class="number">{{ $report['amount'] }}</x-table-cell>
          <x-table-cell>{{ $report['note'] }}</x-table-cell>
          <x-table-cell>{{ $report['payment_method'] }}</x-table-cell>
          </x-table-row>
      @endforeach

      <x-table-row>
        <x-table-cell colspan="2">{{ __('Total') }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['total'] }}</x-table-cell>
        <x-table-cell colspan="2"></x-table-cell>
      </x-table-row>

    </tbody>
  </x-table>
</div>

