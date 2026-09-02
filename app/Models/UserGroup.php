<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // <-- Import BelongsToMany

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

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'category_user_group', // nama tabel pivot
            'usergroupid',         // FK ke UserGroup
            'category_code'        // FK ke Category
        );
    }
}