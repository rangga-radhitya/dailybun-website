<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'user';

    protected $primaryKey = 'iduser';

    public $timestamps = false;

    protected $fillable = ['username', 'password', 'idrole'];

    protected $hidden = ['password'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'idrole', 'idrole');
    }
}
