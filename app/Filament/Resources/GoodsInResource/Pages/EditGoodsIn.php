<?php

namespace App\Filament\Resources\GoodsInResource\Pages;

use App\Filament\Resources\GoodsInResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGoodsIn extends EditRecord
{
    protected static string $resource = GoodsInResource::class;

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