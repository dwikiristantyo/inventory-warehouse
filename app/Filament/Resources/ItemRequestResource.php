<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemRequestResource\Pages;
use App\Models\ItemRequest;
use App\Models\User;
use App\Services\ItemRequestWorkflow;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ItemRequestResource extends Resource
{
    protected static ?string $model = ItemRequest::class;
    protected static string|UnitEnum|null $navigationGroup = 'Transaction';
    protected static ?string $navigationLabel = 'Acceptance';
    protected static ?string $modelLabel = 'Item Request';
    protected static ?string $pluralModelLabel = 'Acceptance';
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->canApproveItemRequests();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['warehouse', 'details.item', 'transaction']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('request_no')->label('Request No.')->searchable()->sortable(),
                TextColumn::make('requester_name')->label('Name')->searchable(),
                TextColumn::make('department')->label('Department')->searchable(),
                TextColumn::make('warehouse.warehouse_name')->label('Warehouse'),
                TextColumn::make('details_summary')
                    ->label('Requested Items')
                    ->state(fn (ItemRequest $record): string => $record->details
                        ->map(fn ($detail): string => "{$detail->item_code} - {$detail->item?->description} ({$detail->quantity} {$detail->uom})")
                        ->implode("\n"))
                    ->wrap(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        ItemRequest::STATUS_ACCEPTED => 'success',
                        ItemRequest::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('transaction.trans_no')->label('Goods Out No.'),
                TextColumn::make('created_at')->label('Submitted')->dateTime()->sortable(),
            ])
            ->actions([
                Action::make('accept')
                    ->label('Accept')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Accept item request')
                    ->visible(fn (ItemRequest $record): bool => $record->status === ItemRequest::STATUS_PENDING)
                    ->action(function (ItemRequest $record): void {
                        $user = auth()->user();
                        abort_unless($user instanceof User, 403);

                        app(ItemRequestWorkflow::class)->accept($record, $user);
                        Notification::make()->title('Request accepted and Goods Out created')->success()->send();
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')->label('Reason')->required()->maxLength(2000),
                    ])
                    ->visible(fn (ItemRequest $record): bool => $record->status === ItemRequest::STATUS_PENDING)
                    ->action(function (ItemRequest $record, array $data): void {
                        $user = auth()->user();
                        abort_unless($user instanceof User, 403);

                        app(ItemRequestWorkflow::class)->reject($record, $user, $data['rejection_reason']);
                        Notification::make()->title('Request rejected')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItemRequests::route('/'),
        ];
    }
}
