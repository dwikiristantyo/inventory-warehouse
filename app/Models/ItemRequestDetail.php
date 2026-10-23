<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemRequestDetail extends Model
{
    protected $fillable = [
        'item_request_id',
        'item_code',
        'quantity',
        'uom',
        'current_balance',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    public function itemRequest(): BelongsTo
    {
        return $this->belongsTo(ItemRequest::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_code', 'item_code');
    }
}
