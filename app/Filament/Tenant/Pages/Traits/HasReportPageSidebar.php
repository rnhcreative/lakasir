<?php

namespace App\Filament\Tenant\Pages\Traits;

use Filament\Pages\Page;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Route;
use App\Filament\Tenant\Pages\MemberReport;
use App\Filament\Tenant\Pages\ExpenseReport;
use App\Filament\Tenant\Pages\ProductReport;
use App\Filament\Tenant\Pages\SellingReport;
use App\Filament\Tenant\Pages\EmployeeReport;
use App\Filament\Tenant\Pages\PurchasingReport;
use App\Filament\Tenant\Pages\ReceivableReport;
use App\Features\Purchasing as PurchasingFeature;
use App\Filament\Tenant\Pages\ReturSellingReport;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;

trait HasReportPageSidebar
{
    use HasPageSidebar;

    public static function sidebar(): FilamentPageSidebar
    {
        $items = [
            static::generateNavigationItem(SellingReport::class)->group(__('Choose Report Type')),
            static::generateNavigationItem(ProductReport::class)->group(__('Choose Report Type')),
            static::generateNavigationItem(MemberReport::class)->group(__('Choose Report Type')),
            static::generateNavigationItem(EmployeeReport::class)->group(__('Choose Report Type')),
            static::generateNavigationItem(ReceivableReport::class)->group(__('Choose Report Type')),
            static::generateNavigationItem(ExpenseReport::class)->group(__('Choose Report Type')),
            static::generateNavigationItem(ReturSellingReport::class)->group(__('Choose Report Type')),
            // @DISABLED
            //static::generateNavigationItem(CashierReport::class),
        ];

        // if (feature(PurchasingFeature::class)) {
        //     $items[] = static::generateNavigationItem(PurchasingReport::class);
        // }

        return FilamentPageSidebar::make()
            ->topbarNavigation()
            ->setNavigationItems($items);
    }

    private static function generateNavigationItem(string $resource, ?string $feature = null): PageNavigationItem
    {
        $canAccess = $feature ? feature($feature) && $resource::canAccess() : $resource::canAccess();

        $active = false;
        if ((new $resource) instanceof Page) {
            $active = Str::of($resource::getRouteName())->exactly(Route::current()->getName());
        }

        if ((new $resource) instanceof Resource) {
            $active = Str::of(Route::currentRouteName())->contains($resource::getRouteBaseName());
        }

        return PageNavigationItem::make($resource::getLabel())
            ->visible($canAccess)
            ->icon($resource::getNavigationIcon())
            ->isActiveWhen(fn (): bool => $active)
            ->url(fn (): string => $resource::getUrl());
    }
}
