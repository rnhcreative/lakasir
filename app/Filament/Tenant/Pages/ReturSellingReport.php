<?php

namespace App\Filament\Tenant\Pages;

use App\Exports\ReturSellingReportExport;
use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Livewire\Attributes\Url;
use Filament\Forms\Contracts\HasForms;
use App\Traits\HasTranslatableResource;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Concerns\InteractsWithFormActions;
use App\Filament\Tenant\Pages\Traits\HasReportPageSidebar;
use App\Filament\Tenant\Pages\Traits\UseDateFilterForm;
use App\Services\Tenants\ReturSellingReportService;

class ReturSellingReport extends Page implements HasActions, HasForms
{
    use HasReportPageSidebar, HasTranslatableResource, InteractsWithFormActions, InteractsWithForms, UseDateFilterForm;

    protected static ?string $title = '';

    public static ?string $label = 'Retur Selling Report';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.tenant.pages.retur-selling-report';

    #[Url]
    public ?array $data = [
        'start_date' => null,
        'end_date' => null,
        'period' => null,
    ];

    public $reports = null;

    public function mount()
    {
        $this->generate(new ReturSellingReportService);
    }

    public function form(Form $form): Form
    {
        return $this->generateDateFilterForm($form);
    }

    public function getFormActions(): array
    {
        return [
            Action::make(__('Generate'))
                ->action('generate'),
            Action::make('download-pdf')
                ->label(__('Download as PDF'))
                ->action('downloadPdf')
                ->color('warning')
                ->icon('heroicon-o-arrow-down-on-square'),
            Action::make('download-xls')
                ->label(__('Download as XLS'))
                ->action('downloadSheet')
                ->color('warning')
                ->icon('heroicon-o-arrow-down-on-square'),
        ];
    }

    public function generate(ReturSellingReportService $returSellingReportService)
    {
        $this->validate([
            'data.start_date' => 'required',
            'data.end_date' => 'required',
            'data.period' => 'required',
        ]);

        $this->reports = $returSellingReportService->generate($this->data);
    }

    public function downloadPdf()
    {
        $this->validate([
            'data.start_date' => 'required',
            'data.end_date' => 'required',
            'data.period' => 'required',
        ]);

        return $this->redirectRoute('retur-selling-report.generate', $this->data);
    }

    public function downloadSheet(ReturSellingReportService $returSellingReportService)
    {
        $this->validate([
            'data.start_date' => 'required',
            'data.end_date' => 'required',
            'data.period' => 'required',
        ]);

        $filename = 'retur-selling-report-'. Carbon::parse($this->data['start_date'])->format('d-m-Y') . '_' . Carbon::parse($this->data['end_date'])->format('d-m-Y')  .'.xlsx';

        return (new ReturSellingReportExport(
            returSellingReportService: $returSellingReportService,
            data: $this->data
        ))->download($filename);
    }
}
