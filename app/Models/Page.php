<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['title', 'content'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'page_role');
    }
}
