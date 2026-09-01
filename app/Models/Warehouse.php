<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'warehouses';
    protected $primaryKey = 'warehouseid'; // Sesuaikan jika nama primary key adalah warehouseid

    protected $fillable = [
        'companyid',
        'warehouse_name',
        'address',
        'status',
    ];

    /**
     * Relasi ke model Company
     */
    public function company(): BelongsTo
    {
        // Parameter: (TargetModel, foreign_key, owner_key)
        return $this->belongsTo(Company::class, 'companyid', 'companyid');
    }
}