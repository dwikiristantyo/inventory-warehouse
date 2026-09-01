<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    /**
     * Relasi ke UserGroup (Role)
     */
    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class, 'usergroupid', 'usergroupid');
    }

    /**
     * Relasi ke model Company (Tabel pivot: user_companies)
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(
            Company::class,
            'user_companies', // Nama tabel pivot sesuai pgAdmin
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
            'user_warehouse', // Nama tabel pivot sesuai pgAdmin
            'user_id',
            'warehouseid',    // Kolom foreign key di user_warehouse
            'id',
            'warehouseid'     // Sesuaikan dengan primary key di tabel warehouses (warehouseid / id)
        );
    }
}