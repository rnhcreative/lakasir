<?php

namespace App\Filament\Tenant\Pages;

use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Livewire\Attributes\Url;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use App\Traits\HasTranslatableResource;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Contracts\HasActions;
use Illuminate\Contracts\Support\Htmlable;
use App\Services\Tenants\ProfitLossService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Concerns\InteractsWithFormActions;

class ProfitLoss extends Page implements HasActions, HasForms
{
    use HasTranslatableResource, InteractsWithForms, InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.tenant.pages.profit-loss';

    protected static ?string $navigationLabel = 'ProfitLoss Report';

    public static function getBreadcrumb(): string
    {
        return __('ProfitLoss Report');
    }

    #[Url]
    public ?array $data = [
        'start_date' => null,
        'end_date' => null,
        'period' => null,
    ];

    public $reports = null;

    public static function getPageNavigationLabel(): string
    {
        return __('ProfitLoss Report');
    }

    public function getTitle(): string | Htmlable
    {
        return __('ProfitLoss Report');
    }

    public function mount()
    {
        $this->generate(new ProfitLossService);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('period')
                ->translateLabel()
                ->options([
                    'today' => __('Today'),
                    'yesterday' => __('Yesterday'),
                    'this_week' => __('This Week'),
                    'last_week' => __('Last Week'),
                    'this_month' => __('This Month'),
                    'last_month' => __('Last Month'),
                    'this_year' => __('This Year'),
                    'last_year' => __('Last Year'),
                    'custom' => __('Custom'),
                ])
                ->required()
                ->live()
                ->afterStateUpdated( function (Get $get, ?string $state) {
                    switch ($state) {
                        case 'today':
                            $this->data['start_date'] = now(config('setting.timezone'))->format('Y-m-d');
                            $this->data['end_date'] = now(config('setting.timezone'))->format('Y-m-d');
                            break;
                        case 'yesterday':
                            $this->data['start_date'] = now(config('setting.timezone'))->subDay()->format('Y-m-d');
                            $this->data['end_date'] = now(config('setting.timezone'))->subDay()->format('Y-m-d');
                            break;
                        case 'this_week':
                            $this->data['start_date'] = now(config('setting.timezone'))->startOfWeek()->format('Y-m-d');
                            $this->data['end_date'] = now(config('setting.timezone'))->endOfWeek()->format('Y-m-d');
                            break;
                        case 'last_week':
                            $this->data['start_date'] = now(config('setting.timezone'))->subWeek()->startOfWeek()->format('Y-m-d');
                            $this->data['end_date'] = now(config('setting.timezone'))->subWeek()->endOfWeek()->format('Y-m-d');
                            break;
                        case 'this_month':
                            $this->data['start_date'] = now(config('setting.timezone'))->startOfMonth()->format('Y-m-d');
                            $this->data['end_date'] = now(config('setting.timezone'))->endOfMonth()->format('Y-m-d');
                            break;
                        case 'last_month':
                            $this->data['start_date'] = now(config('setting.timezone'))->subMonth()->startOfMonth()->format('Y-m-d');
                            $this->data['end_date'] = now(config('setting.timezone'))->subMonth()->endOfMonth()->format('Y-m-d');
                            break;
                        case 'this_year':
                            $this->data['start_date'] = now(config('setting.timezone'))->startOfYear()->format('Y-m-d');
                            $this->data['end_date'] = now(config('setting.timezone'))->endOfYear()->format('Y-m-d');
                            break;
                        case 'last_year':
                            $this->data['start_date'] = now(config('setting.timezone'))->subYear()->startOfYear()->format('Y-m-d');
                            $this->data['end_date'] = now(config('setting.timezone'))->subYear()->endOfYear()->format('Y-m-d');
                            break;
                        case 'custom':
                            $this->data['start_date'] = null;
                            $this->data['end_date'] = null;
                        default:
                            $this->data['start_date'] = null;
                            $this->data['end_date'] = null;
                            break;
                    }
                }),
            Section::make([
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
            ])
                ->columns(2)
                ->visible(fn (Get $get) => $get('period') === 'custom')
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

    public function generate(ProfitLossService $profitLossService)
    {
        $this->validate([
            'data.start_date' => 'required',
            'data.end_date' => 'required',
            'data.period' => 'required',
        ]);

        $this->reports = $profitLossService->generate($this->data);
    }

    public function downloadPdf()
    {
        $this->validate([
            'data.start_date' => 'required',
            'data.end_date' => 'required',
            'data.period' => 'required',
        ]);

        return $this->redirectRoute('profit-loss.generate', $this->data);
    }

    public function downloadSheet(ProfitLossService $profitLossService)
    {
        $this->validate([
            'data.start_date' => 'required',
            'data.end_date' => 'required',
        ]);

        // $filename = 'profit-loss-'. Carbon::parse($this->data['start_date'])->format('d-m-Y') . '_' . Carbon::parse($this->data['end_date'])->format('d-m-Y')  .'.xlsx';

        // return (new CashflowExport(
        //     cashflowService: $profitLossService,
        //     data: $this->data
        // ))->download($filename);
    }
}
