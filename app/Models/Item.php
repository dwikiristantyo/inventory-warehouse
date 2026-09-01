<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $primaryKey = 'item_code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'item_code', 'category_code', 'description', 
        'uprice', 'uom1', 'uom2', 'conv_qty', 'status'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_code', 'category_code');
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class, 'item_code', 'item_code');
    }

    // Checking if item has transactions
    public function hasTransactions(): bool
    {
        return $this->transactionDetails()->exists();
    }
}