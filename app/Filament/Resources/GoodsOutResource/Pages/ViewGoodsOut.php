<?php

namespace App\Filament\Resources\GoodsOutResource\Pages;

use App\Filament\Resources\GoodsOutResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGoodsOut extends ViewRecord
{
    protected static string $resource = GoodsOutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->hidden(fn () => $this->record->status === 'P' || $this->record->isPeriodLocked()),
        ];
    }
}