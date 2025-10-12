<?php

namespace App\Filament\Tenant\Resources\MemberResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Tenant\Resources\MemberResource;

class ViewMember extends ViewRecord
{
    protected static string $resource = MemberResource::class;

    public function getTitle(): string |Htmlable
    {
        $member = $this->record;

        return __('Member').': '.$member->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
