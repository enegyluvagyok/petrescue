<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppBuild extends Model
{
    protected $fillable = [
        'file_name', 'original_name', 'version', 'build_type', 'notes'
    ];
}
