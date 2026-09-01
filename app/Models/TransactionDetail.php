<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $table = 'transaction_details';

    protected $fillable = [
        'trans_id',
        'item_code',
        'qty_uom1',
        'qty_uom2',
    ];

    // Auto-cast angka pecahan (decimal/float) agar tipe data konsisten
    protected $casts = [
        'qty_uom1' => 'float',
        'qty_uom2' => 'float',
    ];

    /**
     * Relasi ke TransactionHeader (Inverse / BelongsTo)
     */
    public function header()
    {
        return $this->belongsTo(TransactionHeader::class, 'trans_id', 'trans_id');
    }

    /**
     * Relasi ke Master Item (BelongsTo)
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_code', 'item_code');
    }
}