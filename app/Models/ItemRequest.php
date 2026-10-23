<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'request_no',
        'requester_email',
        'requester_name',
        'department',
        'warehouseid',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'transaction_id',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(ItemRequestDetail::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouseid', 'warehouseid');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(TransactionHeader::class, 'transaction_id', 'trans_id');
    }
}
