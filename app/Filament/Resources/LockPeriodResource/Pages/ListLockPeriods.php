<?php

namespace App\Filament\Resources\LockPeriodResource\Pages;

use App\Filament\Resources\LockPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLockPeriods extends ListRecords
{
    protected static string $resource = LockPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}