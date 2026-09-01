<?php

namespace App\Filament\Resources\AdjustmentResource\Pages;

use App\Filament\Resources\AdjustmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAdjustment extends ViewRecord
{
    protected static string $resource = AdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->hidden(fn () => $this->record->status === 'P' || $this->record->isPeriodLocked()),
        ];
    }
}