<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model.
     */
    protected $table = 'categories';

    /**
     * Primary key tabel categories.
     */
    protected $primaryKey = 'category_code';

    /**
     * Indikasi bahwa ID tidak bersifat auto-incrementing.
     */
    public $incrementing = false;

    /**
     * Tipe data dari primary key.
     */
    protected $keyType = 'string';

    /**
     * Kolom yang dapat diisi secara massal (mass assignable).
     */
    protected $fillable = [
        'category_code',
        'category_name',
        'status',
    ];

    /**
     * Relasi One-to-Many ke model Item.
     * 1 Category bisa memiliki banyak Item.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'category_code', 'category_code');
    }

    /**
     * Relasi Many-to-Many ke model UserGroup.
     * 1 Category bisa dimiliki oleh banyak UserGroup melalui tabel pivot.
     */
    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            UserGroup::class,             // Model tujuan
            'category_user_group',      // Nama tabel pivot / junction
            'category_code',              // Foreign key pada tabel pivot yang merujuk ke Category
            'usergroupid',                // Foreign key pada tabel pivot yang merujuk ke UserGroup
            'category_code',              // Local key pada model Category
            'usergroupid'                 // Primary key pada model UserGroup
        );
    }
}