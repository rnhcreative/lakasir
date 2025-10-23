<?php

namespace App\Filament\Tenant\Pages;

use App\Exports\CategoryProductReportExport;
use App\Filament\Tenant\Pages\Traits\HasReportPageSidebar;
use App\Filament\Tenant\Pages\Traits\UseDateFilterForm;
use App\Services\Tenants\CategoryProductReportService;
use App\Traits\HasTranslatableResource;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class CategoryProductReport extends Page implements HasActions, HasForms
{
    use HasReportPageSidebar, HasTranslatableResource, InteractsWithFormActions, InteractsWithForms, UseDateFilterForm;

    protected static ?string $title = '';

    public static ?string $label = 'Category Product Report';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.tenant.pages.category-product-report';

    #[Url]
    public ?array $data = [
        'start_date' => null,
        'end_date' => null,
        'period' => null,
    ];

    public $reports = null;

    public function mount()
    {
        $this->generate(new CategoryProductReportService);
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

    public function generate(CategoryProductReportService $categoryProductReportService)
    {
        $this->validate([
            'data.start_date' => 'required',
            'data.end_date' => 'required',
            'data.period' => 'required',
        ]);

        $this->reports = $categoryProductReportService->generate($this->data);
    }

    public function downloadPdf()
    {
        $this->validate([
            'data.start_date' => 'required',
            'data.end_date' => 'required',
            'data.period' => 'required',
        ]);

        return $this->redirectRoute('category-product-report.generate', $this->data);
    }

    public function downloadSheet(CategoryProductReportService $categoryProductReportService)
    {
        $this->validate([
            'data.start_date' => 'required',
            'data.end_date' => 'required',
            'data.period' => 'required',
        ]);

        $filename = 'category-product-report-'. Carbon::parse($this->data['start_date'])->format('d-m-Y') . '_' . Carbon::parse($this->data['end_date'])->format('d-m-Y')  .'.xlsx';

        return (new CategoryProductReportExport(
            categoryProductReportService: $categoryProductReportService,
            data: $this->data
        ))->download($filename);
    }
}
