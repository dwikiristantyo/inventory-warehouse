<?php

namespace App\Models;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGroupDetail extends Model
{
    use HasFactory;

    protected $table = 'user_group_details';

    protected $fillable = [
        'usergroupid',
        'menu_id',
        'can_view',
        'can_add',
        'can_edit',
        'can_delete',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_add' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
    ];

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class, 'usergroupid', 'usergroupid');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id'); // Sesuaikan dengan PK tabel menus
    }
}