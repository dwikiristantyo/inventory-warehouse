<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserGroup extends Model
{
    use HasFactory;

    protected $table = 'user_groups';
    protected $primaryKey = 'usergroupid';

    protected $fillable = [
        'description',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(UserGroupDetail::class, 'usergroupid', 'usergroupid');
    }
}