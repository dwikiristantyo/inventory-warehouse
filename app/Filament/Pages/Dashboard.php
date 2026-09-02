<?php

namespace App\Filament\Pages;

use App\Models\Item;
use App\Models\TransactionHeader;
use App\Models\Warehouse;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Hash;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Inventory Warehouse Dashboard';
    protected static ?int $navigationSort = -2;

    // View disesuaikan ke lokasi view Filament
    protected string $view = 'dashboard';

    // Getter ikon navigasi
    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-home';
    }

    // Mengirim data statistik ke Blade (jika ingin digunakan)
    public function getViewData(): array
    {
        return [
            'stats' => [
                'total_warehouses' => Warehouse::count(),
                'total_items' => Item::count(),
                'total_transactions' => TransactionHeader::count(),
                'latest_warehouses' => Warehouse::with('company')->take(5)->get(),
            ]
        ];
    }

    // Modal Form Change Password
    public function changePasswordAction(): Action
    {
        return Action::make('changePassword')
            ->label('Change Password')
            ->icon('heroicon-o-key')
            ->color('gray')
            ->modalHeading('Ubah Password')
            ->modalWidth('md')
            ->form([
                TextInput::make('current_password')
                    ->label('Password Lama')
                    ->password()
                    ->revealable()
                    ->required()
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                if (! Hash::check($value, auth()->user()->password)) {
                                    $fail('Password lama yang Anda masukkan salah.');
                                }
                            };
                        },
                    ]),

                TextInput::make('new_password')
                    ->label('Password Baru')
                    ->password()
                    ->revealable()
                    ->required()
                    ->different('current_password')
                    ->validationMessages([
                        'different' => 'Password tidak boleh sama dengan sebelumnya',
                    ]),

                TextInput::make('new_password_confirmation')
                    ->label('Input Ulang Password Baru')
                    ->password()
                    ->revealable()
                    ->required()
                    ->same('new_password')
                    ->validationMessages([
                        'same' => 'Password baru tidak sama, silahkan cek kembali',
                    ]),
            ])
            ->action(function (array $data) {
                // Update ke tabel users
                $user = auth()->user();
                $user->update([
                    'password' => Hash::make($data['new_password']),
                ]);

                Notification::make()
                    ->title('Password Berhasil Diperbarui')
                    ->success()
                    ->send();
            });
    }
}