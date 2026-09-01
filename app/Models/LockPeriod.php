<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LockPeriod extends Model
{
    use HasFactory;

    protected $table = 'lock_periods';

    protected $fillable = [
        'year',
        'month',
        'warehouseid',
        'is_locked',
        'locked_by',
    ];

    // Auto-cast tipe data agar konsisten saat diakses
    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'is_locked' => 'boolean',
    ];

    /**
     * Relasi ke Warehouse (BelongsTo)
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouseid', 'warehouseid');
    }

    /**
     * Relasi ke User yang melakukan lock/unlock periode (BelongsTo)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'locked_by', 'id');
    }

    /**
     * Helper scope untuk mempermudah pencarian periode yang terkunci
     */
    public function scopeIsLocked($query, $warehouseId, $year, $month)
    {
        return $query->where('warehouseid', $warehouseId)
            ->where('year', $year)
            ->where('month', $month)
            ->where('is_locked', true);
    }
}