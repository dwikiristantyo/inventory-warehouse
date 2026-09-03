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

    /**
     * Cast properti agar tipe data konsisten saat dibaca Eloquent
     */
    protected $casts = [
        'usergroupid' => 'integer',
    ];

    public function getFilamentName(): string
    {
        return $this->username ?? $this->nik ?? 'User';
    }

    /**
     * Relasi ke UserGroup (Role)
     */
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
     * Helper untuk mengecek permission view berdasarkan ID atau Nama Menu
     */
    public function hasMenuAccess(int|string $menuId): bool
    {
        // 1. Jika user tidak punya group/role, tolak akses
        if (!$this->usergroupid) {
            return false;
        }

        // 2. Bypass jika Super Admin (misal usergroupid === 1)
        if ((int) $this->usergroupid === 1) {
            return true;
        }

        // 3. Pengecekan aman untuk PostgreSQL (mendukung boolean true, 1, '1', 't', 'true')
        return $this->groupDetails()
            ->where('menu_id', (string) $menuId)
            ->where(function ($query) {
                $query->where('can_view', true)
                      ->orWhere('can_view', 1)
                      ->orWhere('can_view', '1')
                      ->orWhere('can_view', 't')
                      ->orWhere('can_view', 'true');
            })
            ->exists();
    }

    /**
     * Relasi ke model Company (Tabel pivot: user_companies)
     */
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

    /**
     * Relasi ke model Warehouse (Tabel pivot: user_warehouse)
     */
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