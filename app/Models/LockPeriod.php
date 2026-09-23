<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
     * Boot function untuk menangani event pencatatan & update status transaksi
     */
    protected static function booted(): void
    {
        static::saved(function (LockPeriod $lockPeriod) {
            if ($lockPeriod->is_locked) {
                // Jika periode di-LOCK: Ubah semua status transaksi aktif ('A') menjadi Posted ('P')
                DB::table('transaction_headers')
                    ->where('warehouseid', $lockPeriod->warehouseid)
                    ->whereYear('trans_date', $lockPeriod->year)
                    ->whereMonth('trans_date', $lockPeriod->month)
                    ->where('status', 'A')
                    ->update([
                        'status' => 'P',
                        'updated_at' => now(),
                    ]);
            } else {
                // Jika periode di-UNLOCK: Kembalikan status transaksi Posted ('P') menjadi Active ('A')
                DB::table('transaction_headers')
                    ->where('warehouseid', $lockPeriod->warehouseid)
                    ->whereYear('trans_date', $lockPeriod->year)
                    ->whereMonth('trans_date', $lockPeriod->month)
                    ->where('status', 'P')
                    ->update([
                        'status' => 'A',
                        'updated_at' => now(),
                    ]);
            }
        });
    }

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