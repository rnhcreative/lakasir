<?php

namespace App\Filament\Tenant\Pages\Traits;

use Filament\Pages\Page;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Route;
use App\Filament\Tenant\Pages\MemberReport;
use App\Filament\Tenant\Pages\CashierReport;
use App\Filament\Tenant\Pages\ProductReport;
use App\Filament\Tenant\Pages\SellingReport;
use App\Filament\Tenant\Pages\EmployeeReport;
use App\Filament\Tenant\Pages\PurchasingReport;
use App\Features\Purchasing as PurchasingFeature;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;

trait HasReportPageSidebar
{
    use HasPageSidebar;

    public static function sidebar(): FilamentPageSidebar
    {
        $items = [
            static::generateNavigationItem(SellingReport::class),
            static::generateNavigationItem(ProductReport::class),
            static::generateNavigationItem(MemberReport::class),
            static::generateNavigationItem(EmployeeReport::class),
            // @DISABLED
            //static::generateNavigationItem(CashierReport::class),
        ];

        if (feature(PurchasingFeature::class)) {
            $items[] = static::generateNavigationItem(PurchasingReport::class);
        }

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
