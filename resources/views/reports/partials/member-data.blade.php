<div class="max-w-full">
  <div class="text-center space-y-2">
    <h1 class="text-3xl font-semibold">{{ __('Member Report') }}</h1>
    <h3 class="text-xl">{{ $header['shop_name'] }}</h3>
  </div>
  <p class="mb-4">{{ __('Period') }}: <b>{{ $header['start_date'] }} - {{ $header['end_date'] }}</b></p>
  <x-table class="w-full table-fixed">
    <x-table-header>
      <x-table-header-cell>{{ __('Name') }}</x-table-header-cell>
      <x-table-header-cell>{{ __('Contact') }}</x-table-header-cell>
      <x-table-header-cell>Total {{ __('Transaction') }}</x-table-header-cell>
      <x-table-header-cell class="number">Total {{ __('Purchasing') }}</x-table-header-cell>
    </x-table-header>

    <tbody>
      @foreach($reports as $key => $report)
        <x-table-row>
          <x-table-cell>{{ $report['name'] }}</x-table-cell>
          <x-table-cell>{{ $report['email'] }}</x-table-cell>
          <x-table-cell class="number">{{ $report['total_transaction'] }}</x-table-cell>
          <x-table-cell class="number">{{ $report['total_selling'] }}</x-table-cell>
        </x-table-row>
      @endforeach
      <x-table-row>
        <x-table-cell colspan="2">{{ __('Total') }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['grand_total_transaction'] }}</x-table-cell>
        <x-table-cell class="number">{{ $footer['grand_total_selling'] }}</x-table-cell>
      </x-table-row>
    </tbody>

  </x-table>
</div>
