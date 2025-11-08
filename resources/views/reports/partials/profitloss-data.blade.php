<div class="max-w-full">
  <div class="text-center space-y-2">
    <h1 class="text-3xl font-semibold">{{ __('ProfitLoss Report') }}</h1>
    <h3 class="text-xl">{{ $header['shop_name'] }}</h3>
  </div>
  <p class="mb-4">{{ __('Period') }}: <b>{{ $header['start_date'] }} - {{ $header['end_date'] }}</b></p>
  <x-table class="w-full table-auto">

      <x-table-header>
        <x-table-header-cell>@lang('Description')</x-table-header-cell>
        <x-table-header-cell class="text-right">@lang('Amount') (Rp)</x-table-header-cell>
      </x-table-header>

    <tbody>
      <x-table-row>
        <x-table-cell>{{ __('Pendapatan Penjualan Tunai') }}</x-table-cell>
        <x-table-cell class="number text-right">{{ $reports['total_non_credit_selling'] }}</x-table-cell>
      </x-table-row>
      <x-table-row>
        <x-table-cell>{{ __('Pendapatan Penjualan Kredit (Piutang)') }}</x-table-cell>
        <x-table-cell class="number text-right">{{ $reports['total_credit_selling'] }}</x-table-cell>
      </x-table-row>
      <x-table-row>
        <x-table-cell>{{ __('Total Penjualan Kotor') }}</x-table-cell>
        <x-table-cell class="number text-right">{{ $reports['total_gross_selling'] }}</x-table-cell>
      </x-table-row>
      <x-table-row>
        <x-table-cell>{{ __('(-) Retur Penjualan')}}</x-table-cell>
        <x-table-cell class="number text-right">({{ $reports['total_retur_selling'] }})</x-table-cell>
      </x-table-row>
      <x-table-row>
        <x-table-cell>{{__('Total Penjualan Bersih')}}</x-table-cell>
        <x-table-cell class="number text-right">{{ $reports['total_net_selling'] }}</x-table-cell>
      </x-table-row>
      <x-table-row>
        <x-table-cell>{{ __('(-) Harga Pokok Penjualan') }}</x-table-cell>
        <x-table-cell class="number text-right">({{ $reports['hpp'] }})</x-table-cell>
      </x-table-row>
      <x-table-row>
        <x-table-cell>{{ __('Laba Kotor') }}</x-table-cell>
        <x-table-cell class="number text-right">{{ $reports['total_gross_profit'] }}</x-table-cell>
      </x-table-row>
      <x-table-row>
        <x-table-cell>{{ __('(-) Biaya Operasional (Pengeluaran)') }}</x-table-cell>
        <x-table-cell class="number text-right">({{ $reports['total_expense'] }})</x-table-cell>
      </x-table-row>
      <x-table-row>
        <x-table-cell>{{ __('Laba Bersih') }}</x-table-cell>
        <x-table-cell class="number text-right">{{ $reports['total_net_profit'] }}</x-table-cell>
      </x-table-row>
    </tbody>
  </x-table>
</div>
