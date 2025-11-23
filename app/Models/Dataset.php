<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    protected $fillable = [
        'name', 'description', 'file_path', 'x_variable', 'y_variable'
    ];
}
