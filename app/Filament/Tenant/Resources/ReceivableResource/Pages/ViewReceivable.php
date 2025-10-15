<?php

namespace App\Filament\Tenant\Resources\ReceivableResource\Pages;

use Livewire\Attributes\On;
use Filament\Actions\Action;
use App\Models\Tenants\Member;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Tenants\ReceivablePayment;
use Illuminate\Contracts\Support\Htmlable;
use App\Services\Tenants\ReceivablePaymentService;
use App\Filament\Tenant\Resources\ReceivableResource;
use App\Filament\Tenant\Resources\Traits\RefreshThePage;
use App\Filament\Tenant\Resources\ReceivableResource\Traits\HasReceivablePaymentForm;

class ViewReceivable extends ViewRecord
{
    use HasReceivablePaymentForm, RefreshThePage;

    protected static string $resource = ReceivableResource::class;

    private $dPService;

    public function __construct()
    {
        $this->dPService = new ReceivablePaymentService();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_payment')
                ->translateLabel()
                ->icon('heroicon-s-credit-card')
                ->model(ReceivablePayment::class)
                ->visible(function () {
                    $restReceivable = $this->record->receivables->sum('rest_receivable');
                    if ($restReceivable > 0 && can('create receivable payment')) {
                        return true;
                    }

                    return false;
                })
                ->form($this->getFormPaymentByMember($this->record))
                ->action(function (array $data, Member $member): void {
                    $this->dPService->createByMember($member, $data);

                    // Show success notification
                    Notification::make()
                        ->title('Pembayaran utang berhasil ditambahkan')
                        ->body('Data pembayaran baru telah disimpan.')
                        ->success()
                        ->send();

                    // Reload the record from the database
                    $this->record->refresh();

                    // Force Livewire to re-render this page
                    $this->dispatch('refreshPage', bubbles: true);
                }),
        ];
    }

    #[On('refreshPage')]
    public function refreshPage(): void
    {
        $this->record->refresh();
    }

    public function getTitle(): string|Htmlable
    {
        $record = $this->record;

        return 'Piutang dari: '.$record->name;
    }
}
