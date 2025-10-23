<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\SellingResource\Pages;
use App\Models\Tenants\Employee;
use App\Models\Tenants\Member;
use App\Models\Tenants\PaymentMethod;
use App\Models\Tenants\Selling;
use App\Traits\HasTranslatableResource;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SellingResource extends Resource
{
    use HasTranslatableResource;

    protected static ?string $model = Selling::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Transactions';

    public static function getBreadcrumb(): string
    {
        return __('Transactions');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('paymentMethod.name')
                    ->searchable()
                    ->label(__('Payment Method'))
                    ->sortable(),
                TextColumn::make('member.name')
                    ->translateLabel()
                    ->default('-')
                    ->searchable(),
                TextColumn::make('employee.name')
                    ->translateLabel()
                    ->default('-')
                    ->searchable(),
                TextColumn::make('date')
                    ->dateTime(timezone: config('setting.timezone'))
                    ->translateLabel()
                    ->sortable(),
                TextColumn::make('total_price')
                    ->label('Sub Total')
                    ->translateLabel()
                    ->sortable()
                    ->money(config('setting.currency')),
                TextColumn::make('discount_price')
                    ->label('Discount')
                    ->translateLabel()
                    ->money(config('setting.currency')),
                TextColumn::make('grand_total_price')
                    ->label('Total')
                    ->translateLabel()
                    ->sortable()
                    ->money(config('setting.currency')),
                TextColumn::make('profit')
                    ->label(__('Profit'))
                    ->translateLabel()
                    ->money(config('setting.currency'))
                    ->getStateUsing(function (Selling $record) {
                        return $record->total_price - $record->total_cost;
                    }),
            ])
            ->searchPlaceholder('Search (Code, User, Customer Number')
            ->header(view('filament.tenant.resources.sellings.headers.overview', [
                'start_date' => request()->input('tableFilters.date.start_date'),
                'end_date' => request()->input('tableFilters.date.end_date'),
            ]))
            ->filters([
                SelectFilter::make('payment_method_id')
                    ->label(__('Payment Method'))
                    ->options(PaymentMethod::pluck('name', 'id')->toArray()),
                SelectFilter::make('employee_id')
                    ->label(__('Employee'))
                    ->options(Employee::pluck('name', 'id')->toArray())
                    ->searchable(),
                SelectFilter::make('member_id')
                    ->label(__('Member'))
                    ->options(Member::pluck('name', 'id')->toArray())
                    ->searchable(),
                Filter::make('date')
                    ->form([
                        DatePicker::make('start_date')
                            ->native(false)
                            ->format('Y-m-d')
                            ->timezone(config('setting.timezone'))
                            ->date()
                            ->closeOnDateSelection(),
                        DatePicker::make('end_date')
                            ->native(false)
                            ->format('Y-m-d')
                            ->timezone(config('setting.timezone'))
                            ->date()
                            ->closeOnDateSelection(),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (! ($data['start_date'] && $data['end_date'])) {
                            return null;
                        }

                        return Carbon::parse($data['start_date'])->toFormattedDateString().' s/d '.Carbon::parse($data['end_date'])->toFormattedDateString();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $startDate = $data['start_date'];
                        $endDate = $data['end_date'];
                        if ($timezone = config('setting.timezone')) {
                            if (! $startDate && ! $endDate) {
                                return $query;
                            }
                            $startDate = Carbon::parse($startDate, $timezone)->setTimezone('UTC');
                            $endDate = Carbon::parse($endDate, $timezone)->addDay()->setTimezone('UTC');
                        }

                        return $query
                            ->when($startDate && $endDate, fn (Builder $builder) => $builder->whereBetween('date', [$startDate, $endDate]));
                    }),
                ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSellings::route('/'),
            'view' => Pages\ViewSelling::route('/{record}'),
        ];
    }
}
