<?php

namespace App\Filament\Resources\GoodsOutResource\Pages;

use App\Filament\Resources\GoodsOutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGoodsOuts extends ListRecords
{
    protected static string $resource = GoodsOutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Barang Keluar'),
        ];
    }
}