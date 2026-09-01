<?php

namespace App\Filament\Resources\GoodsInResource\Pages;

use App\Filament\Resources\GoodsInResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGoodsIn extends ViewRecord
{
    protected static string $resource = GoodsInResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->hidden(fn () => $this->record->status === 'P' || $this->record->isPeriodLocked()),
        ];
    }
}