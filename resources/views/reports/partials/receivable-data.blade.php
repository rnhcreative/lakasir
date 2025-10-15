<div class="max-w-full">
  <div class="text-center space-y-2">
    <h1 class="text-3xl font-semibold">{{ __('Receivable Report') }}</h1>
    <h3 class="text-xl">{{ $header['shop_name'] }}</h3>
  </div>
  <p class="mb-4">{{ __('Period') }}: <b>{{ $header['start_date'] }} - {{ $header['end_date'] }}</b></p>

  <x-table class="w-full table-fixed">
    <x-table-header>
      <x-table-header-cell>{{ __('Date') }}</x-table-header-cell>
      <x-table-header-cell>{{ __('Name') }}</x-table-header-cell>
      <x-table-header-cell>{{ __('Contact') }}</x-table-header-cell>
      <x-table-header-cell>{{ __('Type') }}</x-table-header-cell>
      <x-table-header-cell>{{ __('Amount') }}</x-table-header-cell>
      <x-table-header-cell>{{ __('Selling Code') }}</x-table-header-cell>
      <x-table-header-cell>{{ __('Payment Method') }}</x-table-header-cell>

    </x-table-header>

    <tbody>
      @foreach ($reports as $report)
        <x-table-row>
          <x-table-cell>{{ $report['date'] }}</x-table-cell>
          <x-table-cell>{{ $report['member_name'] }}</x-table-cell>
          <x-table-cell>{{ $report['member_email'] }}</x-table-cell>
          <x-table-cell>{{ $report['type'] == 'debt' ? 'Utang' : 'Pembayaran' }}</x-table-cell>
          <x-table-cell class="number">{{ $report['amount'] }}</x-table-cell>
          <x-table-cell>{{ $report['selling_code'] }}</x-table-cell>
          <x-table-cell>{{ $report['payment_method'] }}</x-table-cell>
        </x-table-row>
      @endforeach
      <x-table-row>
        <x-table-cell colspan="6" class="font-bold">{{ __('Total Receivable') }}</x-table-cell>
        <x-table-cell class="number font-bold">{{ $footer['total_debt'] }}</x-table-cell>
      </x-table-row>
      <x-table-row>
        <x-table-cell colspan="6" class="font-bold">{{ __('Total Payment') }}</x-table-cell>
        <x-table-cell class="number font-bold">{{ $footer['total_payment'] }}</x-table-cell>
      </x-table-row>
      <x-table-row>
        <x-table-cell colspan="6" class="font-bold">{{ __('Total Rest Debt') }}</x-table-cell>
        <x-table-cell class="number font-bold">{{ $footer['total_rest_debt'] }}</x-table-cell>
      </x-table-row>
    </tbody>
  </x-table>
</div>

