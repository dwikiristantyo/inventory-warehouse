<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    // Sesuaikan primary key jika di database bukan 'id' (misal: 'companyid')
    protected $primaryKey = 'companyid';

    protected $fillable = [
        'company_name',
        'alias',
        'business_line',
        'address',
        'status',
    ];
}