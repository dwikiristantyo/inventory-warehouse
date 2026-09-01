<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TransactionHeader extends Model
{
    use HasFactory;

    protected $table = 'transaction_headers';
    protected $primaryKey = 'trans_id';

    protected $fillable = [
        'trans_no',
        'trans_type',
        'trans_date', 
        'warehouseid',
        'remark',
        'status',
        'created_by',
    ];

    // Otomatis convert trans_date menjadi objek Carbon/Date
    protected $casts = [
        'trans_date' => 'date',
    ];

    // Relasi ke TransactionDetail (One to Many)
    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'trans_id', 'trans_id');
    }

    // Relasi ke Warehouse (Belongs To)
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouseid', 'warehouseid');
    }

    // Relasi ke User pembuat transaksi (Belongs To)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // Check if period is locked
    public function isPeriodLocked(): bool
    {
        if (!$this->trans_date) {
            return false;
        }

        // Karena sudah di-cast ke 'date', trans_date otomatis jadi objek Carbon
        $date = Carbon::parse($this->trans_date);

        return LockPeriod::where('warehouseid', $this->warehouseid)
            ->where('year', $date->year)
            ->where('month', $date->month)
            ->where('is_locked', true)
            ->exists();
    }
}