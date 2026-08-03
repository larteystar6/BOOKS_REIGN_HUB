<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable
{
    use HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['username','email','password','role_uuid','status'];
    protected $hidden = ['password'];

    public function role() {
        return $this->belongsTo(Role::class,'role_uuid','uuid');
    }
}
