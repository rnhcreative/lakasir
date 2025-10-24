<div class="max-w-full">
  <div class="text-center space-y-2">
    <h1 class="text-3xl font-semibold">{{ __('Cashflow') }}</h1>
    <h3 class="text-xl">{{ $header['shop_name'] }}</h3>
  </div>
  <p class="mb-4">{{ __('Period') }}: <b>{{ $header['start_date'] }} - {{ $header['end_date'] }}</b></p>
  <x-table class="w-full table-fixed">
    <tbody>
        @foreach($reports as $paymentMethod => $cashflows)
            <x-table-row class="border-b border-t border-gray-300">
                <x-table-cell colspan="5"><span class="text-lg font-bold">{{ $paymentMethod }}</span></x-table-cell>
            </x-table-row>
            <x-table-row class="bg-gray-200">
              <x-table-header-cell>{{ __('Date') }}</x-table-header-cell>
              <x-table-header-cell>{{ __('Description') }}</x-table-header-cell>
              <x-table-header-cell>{{ __('Kas Masuk') }}</x-table-header-cell>
              <x-table-header-cell>{{ __('Kas Keluar') }}</x-table-header-cell>
              <x-table-header-cell>{{ __('Balance') }}</x-table-header-cell>
            </x-table-row>
            @foreach($cashflows['rows'] as $cashflow)
            <x-table-row>
                <x-table-cell>{{ $cashflow->date }}</x-table-cell>
                <x-table-cell>{{ $cashflow->note }}</x-table-cell>
                <x-table-cell class="number">{{ $cashflow->debit }}</x-table-cell>
                <x-table-cell class="number">{{ $cashflow->credit }}</x-table-cell>
                <x-table-cell class="number">{{ $cashflow->saldo }}</x-table-cell>
            </x-table-row>
            @endforeach
            <x-table-row class="bg-gray-100 font-bold">
                <x-table-cell colspan="2">{{ __('Total') }}</x-table-cell>
                <x-table-cell class="number">{{ $cashflows['total_debit'] }}</x-table-cell>
                <x-table-cell class="number">{{ $cashflows['total_credit'] }}</x-table-cell>
                <x-table-cell class="number">{{ $cashflows['ending_balance'] }}</x-table-cell>
            </x-table-row>
        @endforeach
    </tbody>

  </x-table>
</div>
