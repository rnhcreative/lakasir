<?php

namespace App\Filament\Tenant\Pages;

use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Livewire\Attributes\Url;
use App\Exports\ReceivableReportExport;
use Filament\Forms\Contracts\HasForms;
use App\Traits\HasTranslatableResource;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Contracts\HasActions;
use App\Services\Tenants\ReceivableReportService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Concerns\InteractsWithFormActions;
use App\Filament\Tenant\Pages\Traits\HasReportPageSidebar;
use Filament\Forms\Components\Select;

class ReceivableReport extends Page implements HasActions, HasForms
{
    use HasReportPageSidebar, HasTranslatableResource, InteractsWithFormActions, InteractsWithForms;

    protected static ?string $title = '';

    public static ?string $label = 'Receivable Report';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.tenant.pages.receivable-report';

    #[Url]
    public ?array $data = [
        'start_date' => null,
        'end_date' => null,
        'type' => 'all',
    ];

    public $reports = null;

    public function mount()
    {
        $this->generate(new ReceivableReportService);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            DatePicker::make('start_date')
                ->translateLabel()
                ->date()
                ->translateLabel()
                ->required()
                ->closeOnDateSelection()
                ->default(now())
                ->native(false),
            DatePicker::make('end_date')
                ->translateLabel()
                ->date()
                ->translateLabel()
                ->closeOnDateSelection()
                ->required()
                ->default(now())
                ->native(false),
            Select::make('type')
                ->translateLabel()
                ->options([
                    'all' => __('All'),
                    'debt' => __('Debt'),
                    'payment' => __('Payments'),
                ])
                ->default('all')
                ->required(),
        ])
            ->columns(2)
            ->statePath('data');
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

    public function generate(ReceivableReportService $receivableReportService)
    {
        $this->validate([
            'data.start_date' => 'required',
            'data.end_date' => 'required',
            'data.type' => 'required|in:all,debt,payment',
        ]);

        $this->reports = $receivableReportService->generate($this->data);
    }

    public function downloadPdf()
    {
        $this->validate([
            'data.start_date' => 'required',
            'data.end_date' => 'required',
            'data.type' => 'required|in:all,debt,payment',
        ]);

        return $this->redirectRoute('receivable-report.generate', $this->data);
    }

    public function downloadSheet(ReceivableReportService $receivableReportService)
    {
        $this->validate([
            'data.start_date' => 'required',
            'data.end_date' => 'required',
            'data.type' => 'required|in:all,debt,payment',
        ]);

        $filename = 'receivable-report-'. Carbon::parse($this->data['start_date'])->format('d-m-Y') . '_' . Carbon::parse($this->data['end_date'])->format('d-m-Y')  .'.xlsx';

        return (new ReceivableReportExport(
            receivableReportService: $receivableReportService,
            data: $this->data
        ))->download($filename);
    }
}
