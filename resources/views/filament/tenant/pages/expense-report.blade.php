<x-filament-panels::page>
<h1 class="text-lg font-bold">{{ __('Expense Report') }}</h1>
    <x-filament-panels::form
        id="form"
        wire:key="{{ 'forms.' . $this->getFormStatePath() }}"
    >
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    <div id="printable-element" class="max-w-full space-y-2">
      @if($reports)
        @include('reports.partials.expense-data', [
          'header' => $reports['header'],
          'reports' => $reports['reports'],
          'footer' => $reports['footer'],
        ])
      @endif
    </div>
</x-filament-panels::page>

