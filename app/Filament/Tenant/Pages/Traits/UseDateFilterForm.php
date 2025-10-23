<?php

namespace App\Filament\Tenant\Pages\Traits;

use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;

trait UseDateFilterForm
{
    public function generateDateFilterForm(Form $form): Form
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
}
