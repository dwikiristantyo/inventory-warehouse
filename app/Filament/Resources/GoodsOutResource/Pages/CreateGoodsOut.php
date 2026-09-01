<?php

namespace App\Filament\Resources\GoodsOutResource\Pages;

use App\Filament\Resources\GoodsOutResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateGoodsOut extends CreateRecord
{
    protected static string $resource = GoodsOutResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}