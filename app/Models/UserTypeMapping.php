<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTypeMapping extends Model
{
    /** @use HasFactory<\Database\Factories\UserTypeMappingFactory> */
    use HasFactory;

    protected $fillable = ['db_type', 'named_type'];
    public $timestamps = false;
}
