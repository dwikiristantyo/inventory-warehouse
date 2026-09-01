<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}