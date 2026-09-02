<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements HasName
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nik',
        'username',
        'email',
        'password',
        'usergroupid',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getFilamentName(): string
    {
        return $this->username ?? $this->nik ?? 'User';
    }

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class, 'usergroupid', 'usergroupid');
    }

    /**
     * Relasi ke UserGroupDetail
     */
    public function groupDetails(): HasMany
    {
        return $this->hasMany(UserGroupDetail::class, 'usergroupid', 'usergroupid');
    }

    /**
     * Helper untuk mengecek permission view berdasarkan ID / Nama Menu
     */
    public function hasMenuAccess(int|string $menuId): bool
    {
        // Bypass jika Super Admin (misal usergroupid 1)
        if ($this->usergroupid === 1) {
            return true;
        }

        return $this->groupDetails()
            ->where('menu_id', (string) $menuId)
            ->where('can_view', true)
            ->exists();
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(
            Company::class,
            'user_companies',
            'user_id',
            'companyid',
            'id',
            'companyid'
        );
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(
            Warehouse::class,
            'user_warehouse',
            'user_id',
            'warehouseid',
            'id',
            'warehouseid'
        );
    }
}