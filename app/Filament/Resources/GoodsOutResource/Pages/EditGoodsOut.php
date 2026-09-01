<?php

namespace App\Filament\Resources\GoodsOutResource\Pages;

use App\Filament\Resources\GoodsOutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGoodsOut extends EditRecord
{
    protected static string $resource = GoodsOutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}